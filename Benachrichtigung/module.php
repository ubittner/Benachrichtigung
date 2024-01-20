<?php

/**
 * @project       Benachrichtigung/Benachrichtigung/
 * @file          module.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpRedundantMethodOverrideInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection SpellCheckingInspection */
/** @noinspection PhpUnused */

declare(strict_types=1);

include_once __DIR__ . '/helper/BN_autoload.php';

class Benachrichtigung extends IPSModule
{
    //Helper
    use BN_ConfigurationForm;
    use BN_Notification;
    use BN_TriggerCondition;

    //Constants
    private const LIBRARY_GUID = '{76D34084-7695-02EF-DAA8-82041E08AB72}';
    private const MODULE_GUID = '{BDAB70AA-B45D-4CB4-3D65-509CFF0969F9}';
    private const MODULE_PREFIX = 'BN';
    private const WEBFRONT_MODULE_GUID = '{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}';
    private const TILE_VISUALISATION_MODULE_GUID = '{B5B875BB-9B76-45FD-4E67-2607E45B3AC4}';
    private const MAILER_MODULE_GUID = '{C6CF3C5C-E97B-97AB-ADA2-E834976C6A92}';
    private const NEXXTMOBILE_SMS_MODULE_GUID = '{7E6DBE40-4438-ABB7-7EE0-93BC4F1AF0CE}';
    private const SIPGATE_SMS_MODULE_GUID = '{965ABB3F-B4EE-7F9F-1E5E-ED386219EF7C}';
    private const TELEGRAM_BOT_MODULE_GUID = '{32464EBD-4CCC-6174-4031-5AA374F7CD8D}';

    public function Create()
    {
        //Never delete this line!
        parent::Create();

        ########## Properties

        $this->RegisterPropertyString('Note', '');
        $this->RegisterPropertyBoolean('EnableActive', false);
        $this->RegisterPropertyString('TriggerList', '[]');
        $this->RegisterPropertyString('WebFrontNotification', '[]');
        $this->RegisterPropertyString('WebFrontPushNotification', '[]');
        $this->RegisterPropertyString('PostNotification', '[]');
        $this->RegisterPropertyString('Mailer', '[]');
        $this->RegisterPropertyString('NexxtMobile', '[]');
        $this->RegisterPropertyString('Sipgate', '[]');
        $this->RegisterPropertyString('Telegram', '[]');

        ########## Variables

        //Active
        $id = @$this->GetIDForIdent('Active');
        $this->RegisterVariableBoolean('Active', 'Aktiv', '~Switch', 10);
        $this->EnableAction('Active');
        if (!$id) {
            $this->SetValue('Active', true);
        }
    }

    public function ApplyChanges()
    {
        //Wait until IP-Symcon is started
        $this->RegisterMessage(0, IPS_KERNELSTARTED);

        //Never delete this line!
        parent::ApplyChanges();

        //Check runlevel
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        //Delete all references
        foreach ($this->GetReferenceList() as $referenceID) {
            $this->UnregisterReference($referenceID);
        }

        //Delete all update messages
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                if ($message == VM_UPDATE) {
                    $this->UnregisterMessage($senderID, VM_UPDATE);
                }
            }
        }

        $variables = json_decode($this->ReadPropertyString('TriggerList'), true);
        foreach ($variables as $variable) {
            if (!$variable['Use']) {
                continue;
            }
            //Primary condition
            if ($variable['PrimaryCondition'] != '') {
                $primaryCondition = json_decode($variable['PrimaryCondition'], true);
                if (array_key_exists(0, $primaryCondition)) {
                    if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                        $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                        if ($id > 1 && @IPS_ObjectExists($id)) {
                            $this->RegisterReference($id);
                            $this->RegisterMessage($id, VM_UPDATE);
                        }
                    }
                }
            }
            //Secondary condition, multi
            if ($variable['SecondaryCondition'] != '') {
                $secondaryConditions = json_decode($variable['SecondaryCondition'], true);
                if (array_key_exists(0, $secondaryConditions)) {
                    if (array_key_exists('rules', $secondaryConditions[0])) {
                        $rules = $secondaryConditions[0]['rules']['variable'];
                        foreach ($rules as $rule) {
                            if (array_key_exists('variableID', $rule)) {
                                $id = $rule['variableID'];
                                if ($id > 1 && @IPS_ObjectExists($id)) {
                                    $this->RegisterReference($id);
                                }
                            }
                        }
                    }
                }
            }
        }

        //Services
        $services = ['WebFrontNotification', 'WebFrontPushNotification', 'Mailer', 'NexxtMobile', 'Sipgate', 'Telegram'];
        foreach ($services as $service) {
            $variables = json_decode($this->ReadPropertyString($service), true);
            foreach ($variables as $variable) {
                if (!$variable['Use']) {
                    continue;
                }
                $id = $variable['ID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $this->RegisterReference($id);
                }
            }
        }

        //WebFront options
        IPS_SetHidden($this->GetIDForIdent('Active'), !$this->ReadPropertyBoolean('EnableActive'));
    }

    public function Destroy()
    {
        //Never delete this line!
        parent::Destroy();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->SendDebug(__FUNCTION__, $TimeStamp . ', SenderID: ' . $SenderID . ', Message: ' . $Message . ', Data: ' . print_r($Data, true), 0);
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;

            case VM_UPDATE:

                //$Data[0] = actual value
                //$Data[1] = value changed
                //$Data[2] = last value
                //$Data[3] = timestamp actual value
                //$Data[4] = timestamp value changed
                //$Data[5] = timestamp last value

                if ($this->CheckMaintenance()) {
                    return;
                }

                //Check trigger conditions
                $valueChanged = 'false';
                if ($Data[1]) {
                    $valueChanged = 'true';
                }
                $scriptText = self::MODULE_PREFIX . '_CheckTriggerConditions(' . $this->InstanceID . ', ' . $SenderID . ', ' . $valueChanged . ');';
                @IPS_RunScriptText($scriptText);
                break;

        }
    }

    public function CreateInstance(string $ModuleName): void
    {
        $this->SendDebug(__FUNCTION__, 'Modul: ' . $ModuleName, 0);
        switch ($ModuleName) {
            case 'WebFront':
            case 'WebFrontPush':
                $guid = self::WEBFRONT_MODULE_GUID;
                break;

            case 'TileVisualisation':
                $guid = self::TILE_VISUALISATION_MODULE_GUID;
                break;

            case 'Mailer':
                $guid = self::MAILER_MODULE_GUID;
                break;

            case 'SMSNexxtMobile':
                $guid = self::NEXXTMOBILE_SMS_MODULE_GUID;
                break;

            case 'SMSSipgate':
                $guid = self::SIPGATE_SMS_MODULE_GUID;
                break;

            case 'TelegramBot':
                $guid = self::TELEGRAM_BOT_MODULE_GUID;
                break;

            default:
                return;
        }
        $this->SendDebug(__FUNCTION__, 'Guid: ' . $guid, 0);
        $id = @IPS_CreateInstance($guid);
        if (is_int($id)) {
            IPS_SetName($id, 'Mailer');
            $infoText = 'Instanz mit der ID ' . $id . ' wurde erfolgreich erstellt!';
        } else {
            $infoText = 'Instanz konnte nicht erstellt werden!';
        }
        $this->UpdateFormField('InfoMessage', 'visible', true);
        $this->UpdateFormField('InfoMessageLabel', 'caption', $infoText);
    }

    #################### Request Action

    public function RequestAction($Ident, $Value)
    {
        if ($Ident == 'Active') {
            $this->SetValue($Ident, $Value);
        }
    }

    #################### Private

    private function KernelReady()
    {
        $this->ApplyChanges();
    }

    private function CheckMaintenance(): bool
    {
        $result = false;
        if (!$this->GetValue('Active')) {
            $this->SendDebug(__FUNCTION__, 'Abbruch, die Instanz ist inaktiv!', 0);
            $result = true;
        }
        return $result;
    }
}