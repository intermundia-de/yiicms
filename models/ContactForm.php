<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 6/12/19
 * Time: 3:10 PM
 */

namespace intermundia\yiicms\models;

use Yii;
use yii\base\Model;

/**
 * Class ContactForm
 * @package intermundia\yiicms\models
 */
class ContactForm extends Model
{
    public $name;
    public $email;
    public $body;
    public $verifyCheckbox;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [

            // name, email and body are required
            [['name', 'email', 'verifyCheckbox', 'body'], 'required',],
            // We need to sanitize them
//            ['verifyCheckbox', 'required', 'on' => ['contact'], 'requiredValue' => 1, 'message' => 'my test message'],
            [['name', 'body'], 'filter', 'filter' => 'strip_tags'],
            // email has to be a valid email address
            ['email', 'email']

        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('frontend', 'Name'),
            'email' => Yii::t('frontend', 'Email'),
            'body' => Yii::t('frontend', 'Message'),
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     * @param string $email the target email address
     * @return boolean whether the model passes validation
     */
    public function contact($email)
    {

        $pathToBody = 'views/email/body';
        $bodyView = file_exists(Yii::getAlias("@frontend/$pathToBody.php")) ? "@frontend/$pathToBody" : "@cmsCore/$pathToBody";
        
        $websiteContentTree = Yii::$app->websiteContentTree->getModel();
        $ccEmail = $websiteContentTree->activeTranslation->cc_email ?: Yii::$app->params['ccEmail'];
        $bccEmail = $websiteContentTree->activeTranslation->bcc_email ?: Yii::$app->params['bccEmail'];

        if ($this->validate()) {
            $message = Yii::$app->mailer->compose()
                ->setTo($email)
                ->setFrom(Yii::$app->params['robotEmail']);

            if ($ccEmail) {
                $message->setCc($ccEmail);
            }
            if ($bccEmail) {
                $message->setBcc($bccEmail);
            }

            return $message->setReplyTo($this->email)
                ->setSubject(Yii::t('frontend',
                    'Contact request from website: "' . Yii::$app->websiteContentTree->getName() . '"'))
                ->setHtmlBody(Yii::$app->controller->renderPartial($bodyView, ['model' => $this]))
                ->send();
        } else {
            return false;
        }
    }
}

