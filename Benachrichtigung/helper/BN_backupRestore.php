<?php

/** @noinspection PhpUnused */

/*
 * @author      Ulrich Bittner
 * @copyright   (c) 2021
 * @license     CC BY-NC-SA 4.0
 * @see         https://github.com/ubittner/Benachrichtigung/tree/main/Benachrichtigung
 */

declare(strict_types=1);

trait BN_backupRestore
{
    public function CreateBackup(int $BackupCategory): void
    {
        if (IPS_GetInstance($this->InstanceID)['InstanceStatus'] == 102) {
            $name = 'Konfiguration (' . IPS_GetName($this->InstanceID) . ' #' . $this->InstanceID . ') ' . date('d.m.Y H:i:s');
            $config = json_decode(IPS_GetConfiguration($this->InstanceID), true);
            $config['TriggerVariables'] = json_decode($config['TriggerVariables'], true);
            $config['WebFrontNotification'] = json_decode($config['WebFrontNotification'], true);
            $config['WebFrontPushNotification'] = json_decode($config['WebFrontPushNotification'], true);
            $config['Mailer'] = json_decode($config['Mailer'], true);
            $config['NexxtMobile'] = json_decode($config['NexxtMobile'], true);
            $config['Sipgate'] = json_decode($config['Sipgate'], true);
            $config['Telegram'] = json_decode($config['Telegram'], true);
            $json_string = json_encode($config, JSON_HEX_APOS | JSON_PRETTY_PRINT);
            $content = "<?php\n// Backup " . date('d.m.Y, H:i:s') . "\n// ID " . $this->InstanceID . "\n$" . "config = '" . $json_string . "';";
            $backupScript = IPS_CreateScript(0);
            IPS_SetParent($backupScript, $BackupCategory);
            IPS_SetName($backupScript, $name);
            IPS_SetHidden($backupScript, true);
            IPS_SetScriptContent($backupScript, $content);
            echo 'Die Konfiguration wurde erfolgreich gesichert!';
        }
    }

    public function RestoreConfiguration(int $ConfigurationScript): void
    {
        if ($ConfigurationScript != 0 && IPS_ObjectExists($ConfigurationScript)) {
            $object = IPS_GetObject($ConfigurationScript);
            if ($object['ObjectType'] == 3) {
                $content = IPS_GetScriptContent($ConfigurationScript);
                preg_match_all('/\'([^;]+)\'/', $content, $matches);
                $config = json_decode($matches[1][0], true);
                $config['TriggerVariables'] = json_encode($config['TriggerVariables']);
                $config['WebFrontNotification'] = json_encode($config['WebFrontNotification']);
                $config['WebFrontPushNotification'] = json_encode($config['WebFrontPushNotification']);
                $config['Mailer'] = json_encode($config['Mailer']);
                $config['NexxtMobile'] = json_encode($config['NexxtMobile']);
                $config['Sipgate'] = json_encode($config['Sipgate']);
                $config['Telegram'] = json_encode($config['Telegram']);
                IPS_SetConfiguration($this->InstanceID, json_encode($config));
                if (IPS_HasChanges($this->InstanceID)) {
                    IPS_ApplyChanges($this->InstanceID);
                }
            }
            echo 'Die Konfiguration wurde erfolgreich wiederhergestellt!';
        }
    }
}