<?php

/**
 * @project       Benachrichtigung/Benachrichtigung/helper/
 * @file          BN_ConfigurationForm.php
 * @author        Ulrich Bittner
 * @copyright     2023, 2024 Ulrich Bittner
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

/** @noinspection SpellCheckingInspection */
/** @noinspection DuplicatedCode */

declare(strict_types=1);

trait BN_ConfigurationForm
{
    /**
     * Reloads the configuration form.
     *
     * @return void
     */
    public function ReloadConfig(): void
    {
        $this->ReloadForm();
    }

    /**
     * Expands or collapses the expansion panels.
     *
     * @param bool $State
     * false =  collapse,
     * true =   expand
     * @return void
     */
    public function ExpandExpansionPanels(bool $State): void
    {
        for ($i = 1; $i <= 9; $i++) {
            $this->UpdateFormField('Panel' . $i, 'expanded', $State);
        }
    }

    /**
     * Modifies a configuration button.
     *
     * @param string $Field
     * @param string $Caption
     * @param int $ObjectID
     * @return void
     */
    public function ModifyButton(string $Field, string $Caption, int $ObjectID): void
    {
        $state = false;
        if ($ObjectID > 1 && @IPS_ObjectExists($ObjectID)) {
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', $Caption);
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $ObjectID);
    }

    public function ModifyActualVariableStatesConfigurationButton(string $Field, int $VariableID): void
    {
        $state = false;
        if ($VariableID > 1 && @IPS_ObjectExists($VariableID)) {
            $state = true;
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $VariableID . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $VariableID);
    }

    /**
     * Modifies a trigger list configuration button
     *
     * @param string $Field
     * @param string $Condition
     * @return void
     */
    public function ModifyTriggerListButton(string $Field, string $Condition): void
    {
        $id = 0;
        $state = false;
        //Get variable id
        $primaryCondition = json_decode($Condition, true);
        if (array_key_exists(0, $primaryCondition)) {
            if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                if ($id > 1 && @IPS_ObjectExists($id)) {
                    $state = true;
                }
            }
        }
        $this->UpdateFormField($Field, 'caption', 'ID ' . $id . ' Bearbeiten');
        $this->UpdateFormField($Field, 'visible', $state);
        $this->UpdateFormField($Field, 'objectID', $id);
    }

    /**
     * Gets the configuration form.
     *
     * @return false|string
     * @throws Exception
     */
    public function GetConfigurationForm()
    {
        $form = [];

        ########## Elements

        //Configuration buttons
        $form['elements'][0] =
            [
                'type'  => 'RowLayout',
                'items' => [
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration ausklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, true);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration einklappen',
                        'onClick' => self::MODULE_PREFIX . '_ExpandExpansionPanels($id, false);'
                    ],
                    [
                        'type'    => 'Button',
                        'caption' => 'Konfiguration neu laden',
                        'onClick' => self::MODULE_PREFIX . '_ReloadConfig($id);'
                    ]
                ]
            ];

        //Info
        $library = IPS_GetLibrary(self::LIBRARY_GUID);
        $module = IPS_GetModule(self::MODULE_GUID);
        $form['elements'][] = [
            'type'     => 'ExpansionPanel',
            'caption'  => 'Info',
            'name'     => 'Panel1',
            'expanded' => false,
            'items'    => [
                [
                    'type'    => 'Label',
                    'caption' => "ID:\t\t\t" . $this->InstanceID
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Modul:\t\t" . $module['ModuleName']
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Präfix:\t\t" . $module['Prefix']
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Version:\t\t" . $library['Version'] . '-' . $library['Build'] . ', ' . date('d.m.Y', $library['Date'])
                ],
                [
                    'type'    => 'Label',
                    'caption' => "Entwickler:\t" . $library['Author']
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'Note',
                    'caption' => 'Notiz',
                    'width'   => '600px'
                ]
            ]
        ];

        //Trigger list
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel2',
            'caption' => 'Auslöser',
            'items'   => [
                [
                    'type'    => 'PopupButton',
                    'caption' => 'Aktueller Status',
                    'popup'   => [
                        'caption' => 'Aktueller Status',
                        'items'   => [
                            [
                                'type'     => 'List',
                                'name'     => 'ActualVariableStateList',
                                'caption'  => 'Variablen',
                                'add'      => false,
                                'rowCount' => 1,
                                'sort'     => [
                                    'column'    => 'ActualStatus',
                                    'direction' => 'ascending'
                                ],
                                'columns' => [
                                    [
                                        'name'    => 'ActualStatus',
                                        'caption' => 'Aktueller Status',
                                        'width'   => '250px',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'SensorID',
                                        'caption' => 'ID',
                                        'width'   => '80px',
                                        'onClick' => self::MODULE_PREFIX . '_ModifyActualVariableStatesConfigurationButton($id, "ActualVariableStateConfigurationButton", $ActualVariableStateList["SensorID"]);',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'Designation',
                                        'caption' => 'Bezeichnung',
                                        'width'   => '400px',
                                        'save'    => false
                                    ],
                                    [
                                        'name'    => 'UseWebFrontNotification',
                                        'caption' => 'Nachricht',
                                        'width'   => '150px',
                                        'save'    => false,
                                        'edit'    => [
                                            'type' => 'CheckBox'
                                        ]
                                    ],
                                    [
                                        'name'    => 'UseWebFrontPushNotification',
                                        'caption' => 'Push-Nachricht',
                                        'width'   => '150px',
                                        'save'    => false,
                                        'edit'    => [
                                            'type' => 'CheckBox'
                                        ]
                                    ],
                                    [
                                        'name'    => 'UseMailer',
                                        'caption' => 'E-Mail',
                                        'width'   => '150px',
                                        'save'    => false,
                                        'edit'    => [
                                            'type' => 'CheckBox'
                                        ]
                                    ],
                                    [
                                        'name'    => 'UseSMS',
                                        'caption' => 'SMS',
                                        'width'   => '150px',
                                        'save'    => false,
                                        'edit'    => [
                                            'type' => 'CheckBox'
                                        ]
                                    ],
                                    [
                                        'name'    => 'UseTelegram',
                                        'caption' => 'Telegram',
                                        'width'   => '150px',
                                        'save'    => false,
                                        'edit'    => [
                                            'type' => 'CheckBox'
                                        ]
                                    ],
                                    [
                                        'name'    => 'LastUpdate',
                                        'caption' => 'Letzte Aktualisierung',
                                        'width'   => '200px',
                                        'save'    => false
                                    ]
                                ]
                            ],
                            [
                                'type'     => 'OpenObjectButton',
                                'name'     => 'ActualVariableStateConfigurationButton',
                                'caption'  => 'Bearbeiten',
                                'visible'  => false,
                                'objectID' => 0
                            ]
                        ]
                    ],
                    'onClick' => self::MODULE_PREFIX . '_GetActualVariableStates($id);'
                ],
                [
                    'type'     => 'List',
                    'name'     => 'TriggerList',
                    'caption'  => 'Auslöser',
                    'rowCount' => $this->GetRowAmount('TriggerList'),
                    'add'      => true,
                    'delete'   => true,
                    'columns'  => [
                        [
                            'caption' => 'Aktiviert',
                            'name'    => 'Use',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Bezeichnung',
                            'name'    => 'Designation',
                            'onClick' => self::MODULE_PREFIX . '_ModifyTriggerListButton($id, "TriggerListConfigurationButton", $TriggerList["PrimaryCondition"]);',
                            'width'   => '300px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Mehrfachauslösung',
                            'name'    => 'UseMultipleAlerts',
                            'width'   => '200px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Primäre Bedingung',
                            'name'    => 'PrimaryCondition',
                            'width'   => '1000px',
                            'add'     => '',
                            'edit'    => [
                                'type' => 'SelectCondition'
                            ]
                        ],
                        [
                            'caption' => 'Weitere Bedingungen',
                            'name'    => 'SecondaryCondition',
                            'width'   => '1000px',
                            'add'     => '',
                            'edit'    => [
                                'type'  => 'SelectCondition',
                                'multi' => true
                            ]
                        ],
                        [
                            'caption' => 'Auslösender Melder (%1$s)',
                            'name'    => 'TriggeringDetector',
                            'width'   => '800px',
                            'add'     => 0,
                            'edit'    => [
                                'type' => 'SelectVariable'
                            ]
                        ],
                        [
                            'caption' => 'Text der Meldung (maximal 256 Zeichen)',
                            'name'    => 'MessageText',
                            'width'   => '800px',
                            'add'     => '%1$s',
                            'edit'    => [
                                'type'      => 'ValidationTextBox',
                                'multiline' => true
                            ]
                        ],
                        [
                            'caption' => 'Zeitstempel',
                            'name'    => 'UseTimestamp',
                            'width'   => '140px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Nachricht',
                            'name'    => 'UseWebFrontNotification',
                            'width'   => '150px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontNotificationTitle',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Icon',
                            'name'    => 'WebFrontNotificationIcon',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectIcon'
                            ]
                        ],
                        [
                            'caption' => 'Anzeigedauer',
                            'name'    => 'WebFrontNotificationDisplayDuration',
                            'width'   => '200px',
                            'add'     => 0,
                            'visible' => false,
                            'edit'    => [
                                'type'   => 'NumberSpinner',
                                'suffix' => 'Sekunden'
                            ]
                        ],
                        [
                            'caption' => 'Push-Nachricht',
                            'name'    => 'UseWebFrontPushNotification',
                            'width'   => '150px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel der Meldung (maximal 32 Zeichen)',
                            'name'    => 'WebFrontPushNotificationTitle',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Sound',
                            'name'    => 'WebFrontPushNotificationSound',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type'    => 'Select',
                                'options' => [
                                    [
                                        'caption' => 'Standard',
                                        'value'   => ''
                                    ],
                                    [
                                        'caption' => 'Alarm',
                                        'value'   => 'alarm'
                                    ],
                                    [
                                        'caption' => 'Bell',
                                        'value'   => 'bell'
                                    ],
                                    [
                                        'caption' => 'Boom',
                                        'value'   => 'boom'
                                    ],
                                    [
                                        'caption' => 'Buzzer',
                                        'value'   => 'buzzer'
                                    ],
                                    [
                                        'caption' => 'Connected',
                                        'value'   => 'connected'
                                    ],
                                    [
                                        'caption' => 'Dark',
                                        'value'   => 'dark'
                                    ],
                                    [
                                        'caption' => 'Digital',
                                        'value'   => 'digital'
                                    ],
                                    [
                                        'caption' => 'Drums',
                                        'value'   => 'drums'
                                    ],
                                    [
                                        'caption' => 'Duck',
                                        'value'   => 'duck'
                                    ],
                                    [
                                        'caption' => 'Full',
                                        'value'   => 'full'
                                    ],
                                    [
                                        'caption' => 'Happy',
                                        'value'   => 'happy'
                                    ],
                                    [
                                        'caption' => 'Horn',
                                        'value'   => 'horn'
                                    ],
                                    [
                                        'caption' => 'Inception',
                                        'value'   => 'inception'
                                    ],
                                    [
                                        'caption' => 'Kazoo',
                                        'value'   => 'kazoo'
                                    ],
                                    [
                                        'caption' => 'Roll',
                                        'value'   => 'roll'
                                    ],
                                    [
                                        'caption' => 'Siren',
                                        'value'   => 'siren'
                                    ],
                                    [
                                        'caption' => 'Space',
                                        'value'   => 'space'
                                    ],
                                    [
                                        'caption' => 'Trickling',
                                        'value'   => 'trickling'
                                    ],
                                    [
                                        'caption' => 'Turn',
                                        'value'   => 'turn'
                                    ]
                                ]
                            ]
                        ],
                        [
                            'caption' => 'Zielscript',
                            'name'    => 'WebFrontPushNotificationTargetID',
                            'width'   => '200px',
                            'add'     => 0,
                            'visible' => false,
                            'edit'    => [
                                'type' => 'SelectScript'
                            ]
                        ],
                        [
                            'caption' => 'E-Mail',
                            'name'    => 'UseMailer',
                            'width'   => '150px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Betreff',
                            'name'    => 'Subject',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'SMS',
                            'name'    => 'UseSMS',
                            'width'   => '150px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'SMSTitle',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ],
                        [
                            'caption' => 'Telegram',
                            'name'    => 'UseTelegram',
                            'width'   => '150px',
                            'add'     => false,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'caption' => 'Titel',
                            'name'    => 'TelegramTitle',
                            'width'   => '200px',
                            'add'     => '',
                            'visible' => false,
                            'edit'    => [
                                'type' => 'ValidationTextBox'
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('TriggerList', true)
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Anzahl Auslöser: ' . $this->GetElementsAmount('TriggerList')
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'TriggerListConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //WebFront notification
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel3',
            'caption' => 'Nachricht',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'WebFrontNotification',
                    'caption'  => 'Nachricht',
                    'rowCount' => $this->GetRowAmount('WebFrontNotification'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'WebFront',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "WebFrontNotificationConfigurationButton", "ID " . $WebFrontNotification["ID"] . " konfigurieren", $WebFrontNotification["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::WEBFRONT_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('WebFrontNotification', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'WebFrontNotificationConfigurationButton',
                            'caption'  => 'Bearbeiten',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "WebFront");'
                        ]
                    ]
                ]
            ]
        ];

        //WebFront push notification
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel4',
            'caption' => 'Push-Nachricht',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'WebFrontPushNotification',
                    'caption'  => 'Push-Nachricht',
                    'rowCount' => $this->GetRowAmount('WebFrontPushNotification'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'WebFront',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "WebFrontPushNotificationConfigurationButton", "ID " . $WebFrontPushNotification["ID"] . " konfigurieren", $WebFrontPushNotification["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::WEBFRONT_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('WebFrontPushNotification', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'WebFrontPushNotificationConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "WebFrontPush");'
                        ]
                    ]
                ]
            ]
        ];

        //Post notification
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel5',
            'caption' => 'Post-Nachricht',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'PostNotification',
                    'caption'  => 'Post-Nachricht',
                    'rowCount' => $this->GetRowAmount('PostNotification'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Kachel Visualisierung',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "PostNotificationConfigurationButton", "ID " . $PostNotification["ID"] . " konfigurieren", $PostNotification["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::TILE_VISUALISATION_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('PostNotification', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'PostNotificationConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "TileVisualisation");'
                        ]
                    ]
                ]
            ]
        ];

        //E-Mail
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel6',
            'caption' => 'E-Mail',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'Mailer',
                    'caption'  => 'Mailer',
                    'rowCount' => $this->GetRowAmount('Mailer'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Mailer',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "MailerConfigurationButton", "ID " . $Mailer["ID"] . " konfigurieren", $Mailer["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::MAILER_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('Mailer', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'MailerConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "Mailer");'
                        ]
                    ]
                ]
            ]
        ];

        //SMS
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel7',
            'caption' => 'SMS',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'NexxtMobile',
                    'caption'  => 'NexxtMobile',
                    'rowCount' => $this->GetRowAmount('NexxtMobile'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'NeXXt Mobile',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "NexxtMobileConfigurationButton", "ID " . $NexxtMobile["ID"] . " konfigurieren", $NexxtMobile["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::NEXXTMOBILE_SMS_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('NexxtMobile', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'NexxtMobileConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "SMSNexxtMobile");'
                        ]
                    ]
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'     => 'List',
                    'name'     => 'Sipgate',
                    'caption'  => 'Sipgate',
                    'rowCount' => $this->GetRowAmount('Sipgate'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Sipgate',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "SipgateConfigurationButton", "ID " . $Sipgate["ID"] . " konfigurieren", $Sipgate["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::SIPGATE_SMS_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('Sipgate', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'SipgateConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "SMSSipgate");'
                        ]
                    ]
                ]
            ]
        ];

        //Telegram
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel8',
            'caption' => 'Telegram',
            'items'   => [
                [
                    'type'     => 'List',
                    'name'     => 'Telegram',
                    'caption'  => 'Telegram',
                    'rowCount' => $this->GetRowAmount('Telegram'),
                    'add'      => true,
                    'delete'   => true,
                    'sort'     => [
                        'column'    => 'ID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'name'    => 'Use',
                            'caption' => 'Aktiviert',
                            'width'   => '100px',
                            'add'     => true,
                            'edit'    => [
                                'type' => 'CheckBox'
                            ]
                        ],
                        [
                            'name'    => 'ID',
                            'caption' => 'Telegram Bot',
                            'width'   => '800px',
                            'add'     => 0,
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "TelegramConfigurationButton", "ID " . $Telegram["ID"] . " konfigurieren", $Telegram["ID"]);',
                            'edit'    => [
                                'type'     => 'SelectModule',
                                'moduleID' => self::TELEGRAM_BOT_MODULE_GUID
                            ]
                        ]
                    ],
                    'values' => $this->GetRowColors('Telegram', false),
                ],
                [
                    'type'  => 'RowLayout',
                    'items' => [
                        [
                            'type'     => 'OpenObjectButton',
                            'name'     => 'TelegramConfigurationButton',
                            'caption'  => 'Konfigurieren',
                            'visible'  => false,
                            'objectID' => 0
                        ],
                        [
                            'type'    => 'Label',
                            'caption' => ' '
                        ],
                        [
                            'type'    => 'Button',
                            'caption' => 'Neue Instanz erstellen',
                            'onClick' => self::MODULE_PREFIX . '_CreateInstance($id, "TelegramBot");'
                        ]
                    ]
                ]
            ]
        ];

        //Visualisation
        $form['elements'][] = [
            'type'    => 'ExpansionPanel',
            'name'    => 'Panel9',
            'caption' => 'Visualisierung',
            'items'   => [
                [
                    'type'    => 'CheckBox',
                    'name'    => 'EnableActive',
                    'caption' => 'Aktiv'
                ]
            ]
        ];

        ########## Actions

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => 'Schaltelemente'
            ];

        //Test center
        $form['actions'][] =
            [
                'type' => 'TestCenter'
            ];

        $form['actions'][] =
            [
                'type'    => 'Label',
                'caption' => ' '
            ];

        //Registered references
        $registeredReferences = [];
        $references = $this->GetReferenceList();
        $amountReferences = count($references);
        if ($amountReferences == 0) {
            $amountReferences = 3;
        }
        foreach ($references as $reference) {
            $name = 'Objekt #' . $reference . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($reference)) {
                $name = IPS_GetName($reference);
                $location = IPS_GetLocation($reference);
                $rowColor = '#C0FFC0'; //light green
            }
            $registeredReferences[] = [
                'ObjectID'         => $reference,
                'Name'             => $name,
                'VariableLocation' => $location,
                'rowColor'         => $rowColor];
        }

        //Registered messages
        $registeredMessages = [];
        $messages = $this->GetMessageList();
        $amountMessages = count($messages);
        if ($amountMessages == 0) {
            $amountMessages = 3;
        }
        foreach ($messages as $id => $messageID) {
            $name = 'Objekt #' . $id . ' existiert nicht';
            $location = '';
            $rowColor = '#FFC0C0'; //red
            if (@IPS_ObjectExists($id)) {
                $name = IPS_GetName($id);
                $location = IPS_GetLocation($id);
                $rowColor = '#C0FFC0'; //light green
            }
            switch ($messageID) {
                case [10001]:
                    $messageDescription = 'IPS_KERNELSTARTED';
                    break;

                case [10603]:
                    $messageDescription = 'VM_UPDATE';
                    break;

                default:
                    $messageDescription = 'keine Bezeichnung';
            }
            $registeredMessages[] = [
                'ObjectID'           => $id,
                'Name'               => $name,
                'VariableLocation'   => $location,
                'MessageID'          => $messageID,
                'MessageDescription' => $messageDescription,
                'rowColor'           => $rowColor];
        }

        $form['actions'][] = [
            'type'    => 'ExpansionPanel',
            'caption' => 'Entwicklerbereich',
            'items'   => [
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Referenzen',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredReferences',
                    'rowCount' => $amountReferences,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredReferencesConfigurationButton", "ID " . $RegisteredReferences["ObjectID"] . " bearbeiten", $RegisteredReferences["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
                        ]
                    ],
                    'values' => $registeredReferences
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredReferencesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ],
                [
                    'type'    => 'Label',
                    'caption' => ' '
                ],
                [
                    'type'    => 'Label',
                    'caption' => 'Registrierte Nachrichten',
                    'bold'    => true,
                    'italic'  => true
                ],
                [
                    'type'     => 'List',
                    'name'     => 'RegisteredMessages',
                    'rowCount' => $amountMessages,
                    'sort'     => [
                        'column'    => 'ObjectID',
                        'direction' => 'ascending'
                    ],
                    'columns' => [
                        [
                            'caption' => 'ID',
                            'name'    => 'ObjectID',
                            'width'   => '150px',
                            'onClick' => self::MODULE_PREFIX . '_ModifyButton($id, "RegisteredMessagesConfigurationButton", "ID " . $RegisteredMessages["ObjectID"] . " bearbeiten", $RegisteredMessages["ObjectID"]);'
                        ],
                        [
                            'caption' => 'Name',
                            'name'    => 'Name',
                            'width'   => '300px',
                        ],
                        [
                            'caption' => 'Objektbaum',
                            'name'    => 'VariableLocation',
                            'width'   => '700px'
                        ],
                        [
                            'caption' => 'Nachrichten ID',
                            'name'    => 'MessageID',
                            'width'   => '150px'
                        ],
                        [
                            'caption' => 'Nachrichten Bezeichnung',
                            'name'    => 'MessageDescription',
                            'width'   => '250px'
                        ]
                    ],
                    'values' => $registeredMessages
                ],
                [
                    'type'     => 'OpenObjectButton',
                    'name'     => 'RegisteredMessagesConfigurationButton',
                    'caption'  => 'Bearbeiten',
                    'visible'  => false,
                    'objectID' => 0
                ]
            ]
        ];

        //Dummy info message
        $form['actions'][] =
            [
                'type'    => 'PopupAlert',
                'name'    => 'InfoMessage',
                'visible' => false,
                'popup'   => [
                    'closeCaption' => 'OK',
                    'items'        => [
                        [
                            'type'    => 'Label',
                            'name'    => 'InfoMessageLabel',
                            'caption' => '',
                            'visible' => true
                        ]
                    ]
                ]
            ];

        ########## Status

        $form['status'][] = [
            'code'    => 101,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' wird erstellt',
        ];
        $form['status'][] = [
            'code'    => 102,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' ist aktiv',
        ];
        $form['status'][] = [
            'code'    => 103,
            'icon'    => 'active',
            'caption' => $module['ModuleName'] . ' wird gelöscht',
        ];
        $form['status'][] = [
            'code'    => 104,
            'icon'    => 'inactive',
            'caption' => $module['ModuleName'] . ' ist inaktiv',
        ];
        $form['status'][] = [
            'code'    => 200,
            'icon'    => 'inactive',
            'caption' => 'Es ist Fehler aufgetreten, weitere Informationen unter Meldungen, im Log oder Debug!',
        ];

        return json_encode($form);
    }

    ######### Private

    /**
     * Gets the amount of rows of a list.
     *
     * @param string $ListName
     * @return int
     * @throws Exception
     */
    private function GetRowAmount(string $ListName): int
    {
        $elements = json_decode($this->ReadPropertyString($ListName), true);
        $amountRows = count($elements) + 1;
        if ($amountRows == 1) {
            $amountRows = 3;
        }
        return $amountRows;
    }

    /**
     * Gets the color for all rows of a list.
     *
     * @param string $ListName
     * @param bool $CheckConditions
     * @return array
     * @throws Exception
     */
    private function GetRowColors(string $ListName, bool $CheckConditions): array
    {
        $values = [];
        $elements = json_decode($this->ReadPropertyString($ListName), true);
        foreach ($elements as $element) {
            $error = false;
            $rowColor = '#C0FFC0'; //light green
            if (!$element['Use']) {
                $rowColor = '#DFDFDF'; //grey
            }
            if ($CheckConditions) {
                //Primary condition
                if ($element['PrimaryCondition'] != '') {
                    $primaryCondition = json_decode($element['PrimaryCondition'], true);
                    if (array_key_exists(0, $primaryCondition)) {
                        if (array_key_exists(0, $primaryCondition[0]['rules']['variable'])) {
                            $id = $primaryCondition[0]['rules']['variable'][0]['variableID'];
                            if ($id <= 1 || !@IPS_ObjectExists($id)) {
                                $error = true;
                            }
                        }
                    }
                }
                //Secondary condition
                if ($element['SecondaryCondition'] != '') {
                    $secondaryConditions = json_decode($element['SecondaryCondition'], true);
                    if (array_key_exists(0, $secondaryConditions)) {
                        if (array_key_exists('rules', $secondaryConditions[0])) {
                            $rules = $secondaryConditions[0]['rules']['variable'];
                            foreach ($rules as $rule) {
                                if (array_key_exists('variableID', $rule)) {
                                    $id = $rule['variableID'];
                                    if ($id <= 1 || !@IPS_ObjectExists($id)) {
                                        $error = true;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $id = $element['ID'];
                if ($id <= 1 || !@IPS_ObjectExists($id)) {
                    $error = true;
                }
            }
            if ($error) {
                $rowColor = '#FFC0C0'; //red
            }
            $values[] = ['rowColor' => $rowColor];
        }
        return $values;
    }

    /**
     * Gets the amount of elements of a list.
     *
     * @param string $ListName
     * @return int
     * @throws Exception
     */
    private function GetElementsAmount(string $ListName): int
    {
        return count(json_decode($this->ReadPropertyString($ListName), true));
    }
}