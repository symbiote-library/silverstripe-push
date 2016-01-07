<?php
/**
 * A simple email push provider which sends an email to all users.
 *
 * @package silverstripe-push
 */
class EmailPushProvider extends PushNotificationProvider
{

    public function getTitle()
    {
        return _t('Push.EMAIL', 'Email');
    }

    public function sendPushNotification(PushNotification $notification)
    {
        $email = new Email();
        $email->setFrom($this->getSetting('From'));
        $email->setSubject($this->getSetting('Subject'));
        $email->setBody($notification->Content);

        foreach ($notification->getRecipients() as $recipient) {
            $email->setTo($recipient->Email);
            $email->send();
        }
    }

    public function getSettingsFields()
    {
        return new FieldList(array(
            new TextField(
                $this->getSettingFieldName('Subject'),
                _t('Push.EMAILSUBJECT', 'Email Subject'),
                $this->getSetting('Subject')),
            new TextField(
                $this->getSettingFieldName('From'),
                _t('Push.EMAILFROM', 'Email From Address'),
                $this->getSetting('From'))
        ));
    }

    public function setSettings(array $data)
    {
        parent::setSettings($data);

        $this->setSetting('Subject', isset($data['Subject']) ? (string) $data['Subject'] : null);
        $this->setSetting('From', isset($data['From']) ? (string) $data['From'] : null);
    }

    public function validateSettings()
    {
        $result = parent::validateSettings();

        if (!$this->getSetting('Subject')) {
            $result->error(_t(
                'Push.EMAILSUBJECTREQUIRED', 'An email subject is required'
            ));
        }

        return $result;
    }
}
