<?php
namespace JuiceBox\Core;

class Agile {
    private $default_tag;

    public function __construct() {
        if ( !defined('AGILE_DOMAIN') || !defined('AGILE_USER_EMAIL') || !defined('AGILE_API_KEY')) {
            throw new \Exception('AGILE_DOMAIN, AGILE_USER_EMAIL or AGILE_API_KEY not set.');
        }

        $default_tag = [get_option('blogname') . ' Website'];

        add_action( 'gform_after_submission', array($this, 'gform_agile_integration'), 10, 2 );
    }

    public function gform_agile_integration($entry, $form) {
        // Add your form id to the below array if you wish to exclude an forms
        $exclude_forms = [];
        $form_id = $form['id'];
        if(in_array($form_id, $exclude_forms)) {
            return;
        }

        // Map our field labels to their form entry
        $fields = [];
        foreach($form['fields'] as $field) {
            $key = strtolower(str_replace(' ', '_', $field->label));
            $id = $field->id;
            $value = $entry[$id];
            $fields[$key] = $value;
        }

        if(!empty($fields['email'])) {
            // Set this initially for agile primary key
            $email = $fields['email'];

            // To add custom tags to a form just add in a hidden field called tags
            // then populate however you want
            if(!empty($fields['tags'])) {
                $tags = explode(',', $fields['tags']);
                foreach($tags as &$tag) {
                    $tag = trim($tag);
                }
            } else {
                $tags = $this->default_tag;
            }

            $data = [];
            $data['properties'] = [];

            // Add in tags
            $data['tags'] = $tags;
            unset($fields['tags']);

            // System fields to add to data array
            // -- exclude address as its an array and needs
            // -- to be added in a special way
            $system_fields = [
                'first_name',
                'last_name',
                'email',
                'phone',
            ];
            foreach($system_fields as $field) {
                if(!empty($fields[$field])) {
                    $data['properties'][] = [
                        'name' => $field, 
                        'value' => $fields[$field],
                        'type' => 'SYSTEM'
                    ];
                    unset($fields[$field]);
                }
            }

            // Add in address fields - only capturing state at the moment as thats all thats used
            if(!empty($fields['state'])) {
                $address = [
                    'state' => $fields['state']
                ];
                $data['properties'][] = [
                    'name' => 'address',
                    'value' => json_encode($address),
                    'type' => 'SYSTEM'
                ];
                unset($fields['state']);
            }

            // And now we add in the remaining fields as custom fields
            foreach($fields as $key => $field) {
                $data['properties'][] = [
                    'name' => $key,
                    'value' => $field,
                    'type' => 'CUSTOM'
                ];
                unset($fields[$key]);
            }

            $jsonData = json_encode($data);
            $this->newFormSubmission($email, $jsonData, $data['tags']);
        }
    }

    public function newFormSubmission($email, $data, $tags)
    {
        //check if a contact already exists with email provided
        $contactId = $this->searchExistingAgileContact($email);

        if ($contactId) {
            //update tags if exists
            $tags[] = 'contactupdated';
            $tagData = [
                'id' => $contactId,
                'tags' => $tags
            ];
            $data = json_encode($tagData);
            $this->addAgileContactTags($data);
        } else {
            //add new contact if not
            $this->addAgileContact($data);
        }
    }

    private function agileApi($entity, $data, $method, $content_type = 'application/json')
    {
        $agile_url = "https://" . AGILE_DOMAIN . ".agilecrm.com/dev/api/" . $entity;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
        switch ($method) {
            case "POST":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "GET":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                break;
            case "PUT":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                $url = $agile_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-type : $content_type;", 'Accept : application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, AGILE_USER_EMAIL . ':' . AGILE_API_KEY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    private function searchExistingAgileContact($email)
    {
        $searchContacts = $this->agileApi('contacts/search/email/' . $email, null, 'GET', 'application/json');
        $result = json_decode($searchContacts, false, 512, JSON_BIGINT_AS_STRING);
        if ($result) {
            return $result->id;
        }
        return false;
    }

    private function addAgileContact($data)
    {
        $this->agileApi('contacts', $data, 'POST', 'application/json');
    }

    private function updateAgileContact($data)
    {
        $contact = $this->agileApi('contacts/edit-properties', $data, 'PUT', 'application/json');
        if ($contact) {
            return true;
        }
        return false;
    }


    private function addAgileContactTags($data)
    {
        if (!$data) {
            return;
        }

        $this->agileApi('contacts/edit/tags', $data, 'PUT', 'application/json');
    }
}