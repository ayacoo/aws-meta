## Was macht die Extension?

Es gibt einen Hook der Bilder nach dem Upload in TYPO3 via AWS Rekognition API verarbeitet und die Daten in neue Felder
in der Tabelle sys_file_metadata ablegt.

Die Extension wurde mit https://github.com/b13/make aufgesetzt.

---

## Systemvoraussetzungen

- Es wird TYPO3 in Version 11 unterstützt
- PHP 7.4 und 8.0 sollte passen
- AWS Konto mit entsprechenden Keys
- EXT:filemetadata muss installiert sein

---

## AWS Infos

Die Bilder werden direkt zur Amazon hochgeladen. Dies ist performanter als es erstmal im S3 Bucket
abzulegen. https://docs.aws.amazon.com/rekognition/latest/dg/images-bytes.html

Unterstützt werden Bilder mit der Dateiendung JPG und PNG.

Optimal wäre natürlich ein Command, der diesen Job asynchron durchführt.

## AWS Secrets

Um die API nutzen zu können, muss man im Home Verzeichnis ein .aws Verzeichnis anlegen. Dort muss eine config und
credentials Datei liegen.

#### config

```
[profile BENUTZERNAME]
region=eu-central-1
output=text
```

#### credentials

```
[BENUTZERNAME]
aws_access_key_id=
aws_secret_access_key=
```

Zu beachten ist, dass immer dieselbe Region genutzt werden muss. Im Profil, aber auch bei der Bildanalyse. Man kann sich die Einrichtung natürlich bei .ddev in der Config als Hook eintragen.

---

## Installation

- Zu Beginn muss ein `composer install` durchgeführt werden.
- In den Extension Einstellungen muss man die AWS Werte setzen.

---

## Disclaimer

Die Extension wurde während des [brandung][1] Gewerketages entwickelt und im Nachgang noch optimiert. Die Extension dient lediglich Demo- und Schulungszwecken.

---

## Quellen

- https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/Events/Events/Index.html
- https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/Events/Events/Core/Resource/AfterFileAddedEvent.html
- https://github.com/b13/make
- https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_basic-usage.html
- https://docs.aws.amazon.com/rekognition/latest/dg/what-is.html

[1]: https://www.agentur-brandung.de/
