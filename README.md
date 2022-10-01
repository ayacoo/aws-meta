## What does the extension do?

There is a hook that processes images after uploading them to TYPO3 via AWS Rekognition API and puts the data into new fields
in the table sys_file_metadata.

The extension was set up with https://github.com/b13/make.

---

## System requirements

- TYPO3 version 11 is supported
- PHP 7.4 and 8.0 should fit
- AWS account with appropriate keys
- EXT:filemetadata must be installed

---

## AWS Infos

The images are uploaded directly to Amazon. This is better performance than storing it in the S3 bucket.
https://docs.aws.amazon.com/rekognition/latest/dg/images-bytes.html

Images with the file extension JPG and PNG are supported.

Of course, a command that performs this job asynchronously would be ideal.

## AWS Secrets

To be able to use the API, you have to create an .aws directory in the home directory. There must be located a config and credentials file.

#### config

```
[profile USERNAME]
region=eu-central-1
output=text
```

#### credentials

```
[USERNAME]
aws_access_key_id=
aws_secret_access_key=
```

It should be noted that the same region must always be used. In the profile, but also in the image analysis. Of course, you can enter the set-up as a hook in .ddev in the Config.

---

## Installation

- At the beginning, a `composer install` must be executed.
- In the extension settings you have to set the AWS values.

---

## Disclaimer

The extension was developed with the support of the [agency brandung][1] and subsequently optimised. The extension is for demo and training purposes only.

---

## Sources

- https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/Events/Events/Index.html
- https://docs.typo3.org/m/typo3/reference-coreapi/11.5/en-us/Events/Events/Core/Resource/AfterFileAddedEvent.html
- https://github.com/b13/make
- https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/getting-started_basic-usage.html
- https://docs.aws.amazon.com/rekognition/latest/dg/what-is.html

[1]: https://www.agentur-brandung.de/
