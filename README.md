Yii2 CMS core
============
Package contains core models, migrations, behaviors, controllers etc. for the Yii2 CMS

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist intermundia/yiicms "*"
```

or add

```
"intermundia/yiicms": "*"
```

to the require section of your `composer.json` file.


### Upgrading from single site to Multi site

### Important!!! 

Make sure you do not override `ContentTree::getActiveTranslation` and `BaseModel::getActiveTranslation` methods 

---------------

 1. Add user component in `console/config.php`
    ```php
    'user' => [
        'class' => \intermundia\yiicms\web\User::class,
        'enableSession' => false,
        'identityClass' => \intermundia\yiicms\models\User::class
    ]
    ```

 1. Add controllers in `console/config.php`
    ```php
    'sync' => [
        'class' => \intermundia\yiicms\console\controllers\SyncController::class,
    ],
    'utils' => [
        'class' => \intermundia\yiicms\console\controllers\UtilsController::class,
    ],
    ```
    
 3. Configure `mulsitecore` component in `common/config/base.php`
    If you want to have different domains for different environments, better to create ignored file
    and include inside `multisitecore` config
    ```php
    'websites' => [
        'website key1' => [
            'defaultContentId' => "content tree id",
            'masterLanguage' => 'en-US',
            "storageUrl" => 'storage url',
            "domains" => [
                'frontend' => [
                    'domain1' => 'en-US',
                    'domain2' => 'en-US',
                ],
                'backend' => [
                    'domain1' => 'en-US',
                    'domain2' => 'en-US',
                ]
            ]
        ],
        'website key2' => [
            'defaultContentId' => "content tree id",
            'masterLanguage' => 'en-US',
            "storageUrl" => 'storage url',
            "domains" => [
                'frontend' => [
                    'domain1' => 'en-US',
                    'domain2' => 'en-US',
                ],
                'backend' => [
                    'domain1' => 'en-US',
                    'domain2' => 'en-US',
                ]
            ]
        ]
    ]
    ```
    ###[Update]. 
    Each domain now can correspond to associative array in the following format
    ```php
    'domain1' => [
        'language' => 'en-US',
        'isProduction' => false,
    ]
    ```
    #### Full Example:
    ```php
    'mywebsite.com' => [
        'defaultContentId' => 1234,
        'masterLanguage' => 'en-US',
        "storageUrl" => 'mywebsite.com/storage/web',
        "domains" => [
            'frontend' => [
                'mywebsite.com' => [
                    'language' => 'en-US',
                    'isProduction' => true,
                ],
            ],
            'backend' => [
                'admin.mywebsite.com' => 'en-US', // You can leave en-US as string which means that isProduction and isFrontend both are false
            ]   
        ]
    ]
    ```

 1. Add console script to run core migrations in `./migrate` bash script as the first line

    ```php
    php console/yii migrate --migrationPath=@cmsCore/migrations
    ``` 
 
 1. Run migration
    ```bash 
    ./migrate
    ```    
 1. For switch language from 'en' to 'en-US' 
    ```php 
    php console/yii utils/switch-language en en-US
    ```    
     
 1. Read languages from multiSiteCore websites and insert it in language table
    ```php 
    php console/yii sync/languages 
    ```   
 1. Add websites in contentTree
    ```php 
    php console/yii sync/websites 
    ```      
 1. Make `frontend/controllers/ContentTreeController` to be extend of core's `FrontendContentTreeController`

______________________
    
     
1. To copy the website content when you have already run `php console/yii sync/websites `
    ```php 
    php console/yii utils/copy-language $fromWebsiteKey $toWebsiteKey $from $to
    ```                  
     
 1. Copy language inside website
    ```php 
    php console/yii utils/add-language $websiteKey $from $to
    ```                  
______________________
To update alias, alias-path for and corresponding file manager items
run
   ```php 
      php console/yii utils/fix-alias-and-file-manager-items $websiteKey
   ```
`SluggableBehavior` will update `alias` and `alias_path` attributes for each record in `content_tree_translation` table
that belongs to provided `$websiteKey`.
Corresponding `file_manager_item` records are also updated. 
______________________
To sort existing timeline events based on website key
1. Run migrations
    ```php 
    ./migrate
    ```                  
     
 1. Run
    ```php 
    php console/yii utils/fix-timeline-events
    ```                  

### Changing to core backend login

### Important!!! 
1. Delete backend  `SignInController`   or clear and extend from yiicms ` SignInController` .
##### note: You can ethier extend  models in list from core or Just delete it and change usage class.
2. Clear backend model  `AccountForm`  and extend from yiicms model `AccountForm`  .
3. Clear backend model  `LoginForm`  and extend from yiicms model `LoginForm`  .
4. Update backend  web configuration :
	Change user components login url to  `core/sign-in/unlock` and update globalAccess component aswell  `sign-in` to `core/sign-in` .


### Upgrade to v2.0.0 version

1. Run migrations by running: `php console/yii migrate --migrationPath=@cmsCore/migrations`
2. Remove the following modules from `backend/config/web.php`: widget, file, system, translation, rbac, core
3. You can delete `modules` folder from backend completelly
4. Add `core` module to backend modules with its submodules
```php
'core' => [
    'class' => \intermundia\yiicms\Module::class,
    'modules' => [
	'user' => \intermundia\yiicms\modules\user\Module::class,
	'country' => \intermundia\yiicms\modules\country\Module::class,
	'widget' => \intermundia\yiicms\modules\widget\Module::class,
	'translation' => \intermundia\yiicms\modules\translation\Module::class,
	'rbac' => \intermundia\yiicms\modules\rbac\Module::class,
	'file' => \intermundia\yiicms\modules\file\Module::class,
	'system' => \intermundia\yiicms\modules\system\Module::class,
    ]
],
```
5. You can delete the following controllers from `backend/controllers` and their corresponding models and search models: 
 - ContinentController
 - CountryController
 - LanguageController
 - MenuController
 - SearchController
 - SigninController
 - SiteController
 - TimelineEventController
 - UserController
6. You can delete the following folders from `backend/views` and they will be used from core: 
 - country
 - continent
 - layouts
 - search
 - sign-in
 - site
 - timeline-event
 - user
7. Change the `defaultRoute` into `core/timeline-event/index` in `backend/config/web.php`
8. Change `errorAction` field under `errorHandler` component in `backend/config/web.php`
9. Change the following line in `common/config/base.php`
```php
'on missingTranslation' => ['\backend\modules\translation\Module', 'missingTranslation']
```
into 
```php
'on missingTranslation' => ['\intermundia\yiicms\modules\translation\Module', 'missingTranslation']
```
10. Change `loginUrl` of user component in `backend/config/web.php` into `['core/sign-in/login']`
11. If you want to show on timeline only changes done under the current website you should update existing timeline events by running the following command
```php
php console/yii utils/fix-timeline-events
```
12. Use `base` and `translation` keys to specify searchableAttributes for `baseModel` and for `baseTranslationModel`.
inside `editableContent` configuration Change this:
```php
\common\models\ContentTree::TABLE_NAME_PAGE => [
            'class' => \common\models\Page::class,
            'searchableAttributes' => ['teaser_title', 'teaser_description', 'pdf_text'],
      ]
```
into this:
```php
\common\models\ContentTree::TABLE_NAME_PAGE => [
            'class' => \common\models\Page::class,
            'searchableAttributes' => [
                'translation' => ['teaser_title', 'teaser_description', 'pdf_text'],
                'base' => ['product_code']
            ]
      ]
```
