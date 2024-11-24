### Version 2.1
* Korrektur an der "Nach Regenalarm Beschattung erneut prüfen" und "Nach Windalarm Beschattung erneu prüfen" vorgenommen.

### Version 2.0
* Filter hinzugefügt welches ein Delta ausrechnet um zu grosse sprünge bei der Windveränderung zu verwerfen.
* Dazu wird eine neue versteckte Variable "Letzter Wert" eingeügt.

### Version 1.0

#### Folgende Variablen werden hinzugefügt 

* Je eine Integervariable für den oberen und unteren Schwellwert für die Helligkeit.
* Je eine Integervariable für den Sonnenazimut von und bis.
* Je eine Integervariable für den oberen und unteren Schwellwert für die Windgeschwindigkeit.
* Eine Boolean für das aktivieren bzw. deaktivieren der Sonnenautomatik.
* Je eine Boolean um die Wiederholung der Beschattungsprüfung nach dem auflösen von Wind oder Regen.
* Je eine Boolean um den Alarmstatus von Wind oder Regen anzuzeigen.
* Eine Boolean die anzeigt ob die Beschattung aktiviert ist oder nicht.
   
#### Funktionen 

Die Variablen Sonnenazimut, Regensensor und Windsensor werden im Konfigurationsfeld ausgewählt und durch RegisterMessage gespeichert.
* Die Variable "Beschattung aktiv" wird auf true gestellt, wenn: Der Helligkeitswert (ausgelöst durch Sonnenazimut) grösser oder gleich wie der obere Schwellwert ist und der Azimut zwischen dem Wert der Variable "Azimut: Von" und "Azimut: Bis" liegt.
* Die Variable "Beschattung aktiv" wird auf false gestellt, wenn: Der Helligkeitswert (ausgelöst durch Sonnenazimut) kleiner oder gleich ist wie der untere Schwellwert oder der Azimutwert höher als der Wert von "Azimut: Bis" ist.
* Die Variable "Regen?" wird auf true gesetzt, wenn: Der Regensensor aktiv ist.
* Die Variable "Windalarm?" wird auf true gestellt, wenn: Der Windsensorwert grösser oder gleich wie der obere Schwellwert ist.
* Die Variable "Windalarm?" wird auf false gestellt, wenn: Der Windsenorwert kleiner wie der untere Schwellwert ist.

#### Konfigurationsformular

Auswahlmöglichkeit von folgenden Variablen: Lichtsensor, Sonenazimut, Regensensor und Windsensor.
Sonnenazimut, Regensensor und Windsensor werden durch RegisterMessage gespeichert. 
Folgende Funktionen werden ausgelöst:
* Sonnenazimut: Beschattung
* Regensensor: Regenalarm
* Windsensor: Windalarm
