Before working on the plugin you need to install the dependencies for both composer and npm. To do this run the following commands:
composer install
npm ci

To automatically rebuild the frontend script when you make changes run the following command:
npm run watch

To build the plugin for deployment run the following command:
npm run build


The plugin also uses scoping to ensure no compatibility issues with other plugins using the same packages.
The package that handles the scoping is added as a dev-requirement and is called "wpify/scoper".
It should scope the code automatically when you install the dependencies as long as dev dependencies are installed.
If you want to manually scope the code you can run the following command:
composer wpify-scoper install

To update the scoped dependencies you can run the following command:
composer wpify-scoper update
