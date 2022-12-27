<?php

/**
 * @project       Benachrichtigung/Benachrichtigung
 * @file          BN_Notification.php
 * @author        Ulrich Bittner
 * @copyright     2022 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

trait BN_Notification
{
    public function SendWebFrontNotification(string $Title, string $Text, string $Icon, int $DisplayDuration): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        foreach (json_decode($this->ReadPropertyString('WebFrontNotification'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'WFC_SendNotification(' . $id . ', "' . $Title . '", "' . $Text . '", "' . $Icon . '", ' . $DisplayDuration . ');';
            IPS_RunScriptText($scriptText);
        }
    }

    public function SendWebFrontPushNotification(string $Title, string $Text, string $Sound, int $TargetID = 0): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        //Title length max 32 characters
        $Title = substr($Title, 0, 32);
        //Text length max 256 characters
        $Text = substr($Text, 0, 256);
        foreach (json_decode($this->ReadPropertyString('WebFrontPushNotification'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'WFC_PushNotification(' . $id . ', "' . $Title . '", "' . $Text . '", "' . $Sound . '", ' . $TargetID . ');';
            IPS_RunScriptText($scriptText);
        }
    }

    public function SendMailNotification(string $Subject, string $Text): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        foreach (json_decode($this->ReadPropertyString('Mailer'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'MA_SendMessage(' . $id . ', "' . $Subject . '", "' . $Text . '");';
            IPS_RunScriptText($scriptText);
        }
    }

    public function SendNexxtMobileSMS(string $Text): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        //Text length max 160 characters
        $Text = substr($Text, 0, 160);
        foreach (json_decode($this->ReadPropertyString('NexxtMobile'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'SMSNM_SendMessage(' . $id . ', "' . $Text . '");';
            IPS_RunScriptText($scriptText);
        }
    }

    public function SendSipgateSMS(string $Text): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        //Text length max 160 characters
        $Text = substr($Text, 0, 160);
        foreach (json_decode($this->ReadPropertyString('Sipgate'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'SMSSG_SendMessage(' . $id . ', "' . $Text . '");';
            IPS_RunScriptText($scriptText);
        }
    }

    public function SendTelegramMessage(string $Text): void
    {
        $this->SendDebug(__FUNCTION__, 'wird ausgeführt', 0);
        if ($this->CheckMaintenance()) {
            return;
        }
        foreach (json_decode($this->ReadPropertyString('Telegram'), true) as $element) {
            if (!$element['Use']) {
                continue;
            }
            $id = $element['ID'];
            if ($id <= 1 || @!IPS_ObjectExists($id)) {
                continue;
            }
            $scriptText = 'TB_SendMessage(' . $id . ', "' . $Text . '");';
            IPS_RunScriptText($scriptText);
        }
    }
}