# WordPress by Juicebox

This build is loosely based on the [Roots.io Bedrock](https://roots.io/bedrock/) framework, boasts an enhanced folder structure that protects sensitive files from the web root and features a renamed `app` folder for improved content representation. Public files are stored in the `www` directory.

Composer manages dependencies, while environment-specific files and Dotenv simplify configuration. The build also contains a `mu-plugins` autoloader, which enhances security by enabling regular plugins to function as must-use plugins. Furthermore, a custom theme has been developed using [Timber](https://timber.github.io/docs/) and [Twig](https://twig.symfony.com/).

## Requirements

-   PHP >= 8.0
-   Composer >= 2.0
-   Node.js >= 16.0
-   NPM >= 6.0
-   MySQL >= 5.7 or MariaDB >= 10.6

## Installation

1. Clone the repository (replace domain.com with the repository name):

```
git clone git@bitbucket.org:JuiceBoxCreative/domain.com.git domain
cd domain
```

2. Install PHP dependencies using Composer:

```
composer install
```

3. Install theme dependencies and build assets using NPM:

```
npm install
npm run dev
```

To auto-compile your assets as you are working on the project, run:

```
npm run watch
```

4. Configure your environment variables by creating a `.env` file in the root directory:

```
cp .env.dev .env
```

Edit the `.env` file and set the required variables:

```
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password
DB_HOST=your_database_host
```

5. Set up the database in your local environment by importing a copy from either the staging or production environment. Use MigrateDB to handle the database export from either environment.

## Updating Dependencies

To keep your project up-to-date and secure, it's essential to update dependencies regularly. This includes both PHP and theme dependencies.

### PHP Dependencies

PHP dependencies are managed using Composer. To update them, run the following command in the root directory:

```
composer update
```

This will update all PHP packages to their latest versions, according to the version constraints specified in your `composer.json` file. After updating, make sure to test your application to ensure everything is working as expected.

### Theme Dependencies

Theme dependencies are managed using NPM. To update them, navigate to your site root directory and run the following commands:

```
npm update
```

This will update all packages to their latest versions, according to the version constraints specified in your `package.json` file. After updating, make sure to test your theme to ensure everything is working as expected.

Remember to commit any changes to your `composer.lock`, `package-lock.json`, or `yarn.lock` files after updating dependencies, so that the updated versions are used in all environments.

## Deployment

When pull requests are merged from the `develop` or `master` branches, the code is automatically deployed to the appropriate server. Make sure your changes have been thoroughly tested before merging to ensure a smooth deployment process.

## Support

For any issues, please refer to additional documentation:

-   [Juicbox Documentation](https://app.nuclino.com/Juicebox-Creative/7-Technology/WordPress-502ce3e7-7f16-4366-b190-83f19f069406)
-   [Bedrock Documentation](https://roots.io/bedrock/)
-   [Timber V1 Documentation](https://timber.github.io/docs/)
-   [Twig Documentation](https://twig.symfony.com/)

If you still need help, feel free to ask for help by emailing support@juicebox.com.au.

## License

This project is open-source and licensed under the MIT License.

Copyright 2023 Juicebox

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
