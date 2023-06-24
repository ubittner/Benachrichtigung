<?php

/**
 * @project       Benachrichtigung/Benachrichtigung
 * @file          BN_TriggerCondition.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUndefinedFunctionInspection */

declare(strict_types=1);

trait BN_TriggerCondition
{
    /**
     * Checks the trigger conditions.
     *
     * @param int $SenderID
     * @param bool $ValueChanged
     * false =  same value
     * true =   new value
     *
     * @throws Exception
     */
    public function CheckTriggerConditions(int $SenderID, bool $ValueChanged): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        $this->SendDebug(__FUNCTION__, 'Sender: ' . $SenderID, 0);
        $valueChangedText = 'nicht ';
        if ($ValueChanged) {
            $valueChangedText = '';
        }
        $this->SendDebug(__FUNCTION__, 'Der Wert hat sich ' . $valueChangedText . 'geändert', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $key => $variable) {
            if (!$variable['Use']) {
                continue;
            }
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($SenderID == $id) {
                            $this->SendDebug(__FUNCTION__, 'Listenschlüssel: ' . $key, 0);
                            if (!$variable['UseMultipleAlerts'] && !$ValueChanged) {
                                $this->SendDebug(__FUNCTION__, 'Abbruch, die Mehrfachauslösung ist nicht aktiviert!', 0);
                                continue;
                            }
                            $execute = true;
                            //Check primary condition
                            if (!IPS_IsConditionPassing($variable['PrimaryCondition'])) {
                                $execute = false;
                            }
                            //Check secondary condition
                            if (!IPS_IsConditionPassing($variable['SecondaryCondition'])) {
                                $execute = false;
                            }
                            if (!$execute) {
                                $this->SendDebug(__FUNCTION__, 'Abbruch, die Bedingungen wurden nicht erfüllt!', 0);
                            } else {
                                $this->SendDebug(__FUNCTION__, 'Die Bedingungen wurden erfüllt.', 0);
                                //Prepare data
                                $messageText = $variable['MessageText'];
                                if ($variable['UseTimestamp']) {
                                    $messageText = $messageText . ' ' . date('d.m.Y, H:i:s');
                                }
                                //Create message text
                                $triggeringDetector = $variable['TriggeringDetector'];
                                if ($triggeringDetector > 1 && @IPS_ObjectExists($triggeringDetector)) {
                                    $messageText = sprintf($messageText, GetValueString($triggeringDetector));
                                }
                                $this->SendDebug(__FUNCTION__, 'Nachricht: ' . $messageText, 0);
                                //WebFront notification
                                if ($variable['UseWebFrontNotification']) {
                                    $this->SendWebFrontNotification($variable['WebFrontNotificationTitle'], "\n" . $messageText, $variable['WebFrontNotificationIcon'], $variable['WebFrontNotificationDisplayDuration']);
                                }
                                //WebFront push notification
                                if ($variable['UseWebFrontPushNotification']) {
                                    $this->SendWebFrontPushNotification($variable['WebFrontPushNotificationTitle'], "\n" . $messageText, $variable['WebFrontPushNotificationSound'], $variable['WebFrontPushNotificationTargetID']);
                                }
                                //E-Mail
                                if ($variable['UseMailer']) {
                                    $this->SendMailNotification($variable['Subject'], "\n\n" . $messageText);
                                }
                                //SMS
                                if ($variable['UseSMS']) {
                                    $this->SendNexxtMobileSMS($variable['SMSTitle'] . "\n\n" . $messageText);
                                    $this->SendSipgateSMS($variable['SMSTitle'] . "\n\n" . $messageText);
                                }
                                //Telegram
                                if ($variable['UseTelegram']) {
                                    $this->SendTelegramMessage($variable['TelegramTitle'] . "\n\n" . $messageText);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}