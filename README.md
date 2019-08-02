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
                'domain1' => 'en-US',
                'domain2' => 'en-US',
            ]
        ],
        'website key2' => [
            'defaultContentId' => "content tree id",
            'masterLanguage' => 'en-US',
            "storageUrl" => 'storage url',
            "domains" => [
                'domain1' => 'en-US',
                'domain2' => 'en-US',
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
### Changing to core backend login

### Important!!! 
1. Delete backend  `SignInController`   or clear and extend from yiicms ` SignInController` .
##### note: You can ethier extend  models in list from core or Just delete it and change usage class.
2. Clear backend model  `AccountForm`  and extend from yiicms model `AccountForm`  .
3. Clear backend model  `LoginForm`  and extend from yiicms model `LoginForm`  .
4. Update backend  web configuration :
	Change user components login url to  `core/sign-in/unlock` and update globalAccess component aswell  `sign-in` to `core/sign-in` .

##### note: You can  skip step 4 by redirecting `sign-in` controller actions to `core/sign-in` controller actions or by not deleting `SignInController`.
______________________
