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
     * @param string $contactView the view of email body
     * @return boolean whether the model passes validation
     */
    public function contact($email, $contactView = null)
    {
        $emails = explode(',', $email);

        if (!$contactView) {
            $pathToContact = 'mail/contact';
            $contactView = file_exists(Yii::getAlias("@frontend/$pathToContact.php")) ? "@frontend/$pathToContact" : "@cmsCore/$pathToContact";
        }

        $websiteContentTree = Yii::$app->websiteContentTree->getModel();
        //Make sure .env configuration dor ccEmail and bccEmail are correct
        $ccEmail = $websiteContentTree->activeTranslation->cc_email ?: Yii::$app->params['ccEmail'];
        $bccEmail = $websiteContentTree->activeTranslation->bcc_email ?: Yii::$app->params['bccEmail'];
        $ccEmails = $ccEmail != '' ? explode(',', $ccEmail) : [];
        $bccEmails = $bccEmail != '' ? explode(',', $bccEmail) : [];

        if ($this->validate()) {
            $message = Yii::$app->mailer->compose($contactView, ['model' => $this])
                ->setTo($emails)
                ->setFrom(Yii::$app->params['robotEmail']);

            if (count($ccEmails) > 0) {
                $message->setCc($ccEmails);
            }
            if (count($bccEmails) > 0) {
                $message->setBcc($bccEmails);
            }
            return $message->setReplyTo($this->email)
                ->setSubject(Yii::t('frontend',
                    'Contact request from website: "' . Yii::$app->websiteContentTree->getName() . '"'))
                ->send();
        } else {
            return false;
        }
    }
}

