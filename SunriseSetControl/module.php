<?php

// Klassendefinition
class SunriseSetControl extends IPSModule {
    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create() {
        parent::Create();

        // Profile
        if(!IPS_VariableProfileExists('SunriseSetDelay')) {
            IPS_CreateVariableProfile('SunriseSetDelay', 1);
            IPS_SetVariableProfileValues('SunriseSetDelay', -120.0, 120.0, 1.0);
            IPS_SetVariableProfileText('SunriseSetDelay', '', ' Min.');
        }
        if(!IPS_VariableProfileExists('SunriseSunset')) {
            IPS_CreateVariableProfile('SunriseSunset', 0);
            IPS_SetVariableProfileAssociation('SunriseSunset', false, $this->Translate('Sunset'), '', -1);
            IPS_SetVariableProfileAssociation('SunriseSunset', true, $this->Translate('Sunrise'), '', -1);
        }

        // variable
        $this->RegisterVariableBoolean('SUNRISE_STATE', $this->Translate('Sunrise state'), '~Switch', 0);
        $this->RegisterVariableBoolean('SUNSET_STATE', $this->Translate('Sunset state'), '~Switch', 1);
        $this->EnableAction('SUNRISE_STATE');
        $this->EnableAction('SUNSET_STATE');

        $this->RegisterVariableInteger('SUNRISE_TIME', $this->Translate('Current sunrise time'), '~UnixTimestamp', 2);
        $this->RegisterVariableInteger('SUNRISE_DELAY', $this->Translate('Sunrise delay'), 'SunriseSetDelay', 3);
        $this->RegisterVariableInteger('DELAYED_SUNRISE_TIME', $this->Translate('Delayed sunrise time'), '~UnixTimestamp', 4);

        $this->RegisterVariableInteger('SUNSET_TIME', $this->Translate('Current sunset time'), '~UnixTimestamp', 5);
        $this->RegisterVariableInteger('SUNSET_DELAY', $this->Translate('Sunset delay'), 'SunriseSetDelay', 6);
        $this->RegisterVariableInteger('DELAYED_SUNSET_TIME', $this->Translate('Delayed sunset time'), '~UnixTimestamp', 7);
        
        $this->EnableAction('SUNRISE_DELAY');
        $this->EnableAction('SUNSET_DELAY');


        $this->RegisterVariableBoolean('SUNRISE_SUNSET', $this->Translate('Sunset state'), 'SunriseSunset', 8);

        // Property
        $this->RegisterPropertyInteger('Location', 0);

        // Atributes

        // Register a Timer with an Intervall of 0 milliseconds (initial deaktiviert)
        $this->RegisterTimer('EDITED_SUNRISE', 0, 'BRELAG_TimerAction($_IPS[\'TARGET\'], true);');
        $this->RegisterTimer('EDITED_SUNSET', 0, 'BRELAG_TimerAction($_IPS[\'TARGET\'], false);');  

        // RegisterMessages
        // $this->RegisterMessage(IPS_GetObjectIDByIdent('Sunrise', $this->ReadPropertyInteger('Location')), 10603);
        // $this->RegisterMessage(IPS_GetObjectIDByIdent('Sunset', $this->ReadPropertyInteger('Location')), 10603);
        $this->RegisterMessage($this->GetIDForIdent('SUNRISE_DELAY'), 10603);
        $this->RegisterMessage($this->GetIDForIdent('SUNSET_DELAY'), 10603);

        // Set the current sunrise and sunset time
        // $this->SetCurrentSunsetRiseTime();
    }

    public function RequestAction($Ident, $Value) {
        $IdentID = $this->GetIDForIdent($Ident);
        if($IdentID) {
            SetValue($IdentID, $Value);
        }
    }

    public function ApplyChanges() {
        parent::ApplyChanges();

        // Timer für die tägliche Ausführung konfigurieren
        $this->ConfigureDelayedTime();
        // RegisterMessages
        $this->RegisterMessage(IPS_GetObjectIDByIdent('Sunrise', $this->ReadPropertyInteger('Location')), 10603);
        $this->RegisterMessage(IPS_GetObjectIDByIdent('Sunset', $this->ReadPropertyInteger('Location')), 10603);
        $this->SetCurrentSunsetRiseTime();
    }

    private function GetCurrenSunriseTime() {
        $currentSunriseTime = GetValue(IPS_GetObjectIDByIdent('Sunrise', $this->ReadPropertyInteger('Location'))); 
        return $currentSunriseTime;
    }

    private function GetCurrenSunsetTime() {
        $currentSunsetTime = GetValue(IPS_GetObjectIDByIdent('Sunset', $this->ReadPropertyInteger('Location')));
        return $currentSunsetTime;
    }

    function SetCurrentSunsetRiseTime() {
        $nextSunrise = $this->GetCurrenSunriseTime();
        $nextSunset = $this->GetCurrenSunsetTime();

        SetValue($this->GetIDForIdent('SUNRISE_TIME'), $nextSunrise); 
        SetValue($this->GetIDForIdent('SUNSET_TIME'), $nextSunset);
        $this->SendLogMessage('Current sunrise time: ' . date("d.m.Y - H:i:s", $nextSunrise) . ' - Variable ID: ' . $this->GetIDForIdent('SUNRISE_TIME'));
        $this->SendLogMessage('Current sunset time: ' . date("d.m.Y - H:i:s", $nextSunset) . ' - Variable ID: ' . $this->GetIDForIdent('SUNSET_TIME'));
    }

    public function ConfigureDelayedTime() {
        // Current Zeit
        $now = time();

        // current sunrise time
        $currentSunriseTime = $this->GetCurrenSunriseTime();
        $sunriseDelayInSeconds = GetValue($this->GetIDForIdent('SUNRISE_DELAY')) * 60;
        $delayedSunriseTime = $currentSunriseTime + $sunriseDelayInSeconds;
        // current sunset time
        $currentSunsetTime = $this->GetCurrenSunsetTime();
        $sunsetDelayInSeconds = GetValue($this->GetIDForIdent('SUNSET_DELAY')) * 60;
        $delayedSunsetTime = $currentSunsetTime + $sunsetDelayInSeconds;

        SetValue($this->GetIDForIdent('DELAYED_SUNRISE_TIME'), $delayedSunriseTime);
        SetValue($this->GetIDForIdent('DELAYED_SUNSET_TIME'), $delayedSunsetTime);
        $this->SendLogMessage('New sunrise time: ' . date('d.M.Y - H:i:s', $delayedSunriseTime));
        $this->SendLogMessage('New sunset time: ' . date('d.M.Y - H:i:s', $delayedSunsetTime));

        // remaining seconds to target time
        $intervalToDelayedSunrise = $delayedSunriseTime - $now;
        $intervalToDelayedSunset = $delayedSunsetTime - $now;
        $this->SendLogMessage('Interval to sunrise: ' . $intervalToDelayedSunrise);
        $this->SendLogMessage('Interval to sunset: ' . $intervalToDelayedSunset);

        // Set intervall for the timers, unit: milliseconds
        $this->SetTimerInterval('EDITED_SUNRISE', $intervalToDelayedSunrise * 1000);
        $this->SetTimerInterval('EDITED_SUNSET', $intervalToDelayedSunset * 1000);
    }

    private function SendLogMessage($message) {
        IPS_LogMessage('SunriseSetControl', $message);
    }

    public function TimerAction($value) {
        if($value) {
            if(GetValue($this->GetIDForIdent('SUNRISE_STATE'))) {
                SetValue($this->GetIDForIdent('SUNRISE_SUNSET'), true);
                $this->SendLogMessage('Sunrise action');
            }
        } else {
            if(GetValue($this->GetIDForIdent('SUNSET_STATE'))) {
                SetValue($this->GetIDForIdent('SUNRISE_SUNSET'), false);
                $this->SendLogMessage('Sunset action');
            }
        }
        // Reconfigure the timers
        $this->ConfigureDelayedTime();
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {

        $sunriseVariableID = IPS_GetObjectIDByIdent('Sunrise', IPS_GetObjectIDByName($this->Translate('Location'), 0));
        $sunsetVariableID = IPS_GetObjectIDByIdent('Sunset', IPS_GetObjectIDByName($this->Translate('Location'), 0));

        switch ($SenderID) {
            case $sunriseVariableID:
            case $sunsetVariableID:
            case $this->GetIDForIdent('SUNRISE_DELAY'):
            case $this->GetIDForIdent('SUNSET_DELAY'):
                $this->SetCurrentSunsetRiseTime();
                $this->ConfigureDelayedTime();
                break;
            }
    }
}
