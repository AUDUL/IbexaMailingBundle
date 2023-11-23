# Installation

## Installation steps

Run `composer require code-rhapsodie/ibexamailingbundle` to install the bundle and its dependencies:

### Register the bundle

Activate the bundle in `app\AppKernel.php` file.

```php
// app\AppKernel.php

public function registerBundles()
{
   ...
   $bundles = array(
       ...
       // IbexaMailingBundle
       new CodeRhapsodie\IbexaMailingBundle\IbexaMailingBundle(),
   );
   ...
}
```

### Add routes

```yaml
_ibexamailing_routes:
    resource: '@IbexaMailingBundle/Resources/config/routing.yml'
```

### Add configuration

You need to declare a template for the view `ibexamailingfull`

```yaml
ezpublish:
    system:
        default:
            content_view:
                ibexamailingfull:
                    folder:
                        template: yourtemplatepath
                        match:
                            Identifier\ContentType: [a_content_type]
```

> Adapt according to your configuration


You also need 2 mailers, 1 in charge to send the Mailings, the other to send the service emails.

```yaml
ibexamailing:
    system:
        default:
            simple_mailer: "swiftmailer.mailer.local_mailer"
            mailing_mailer: "swiftmailer.mailer.remote_mailer"
            # Default email values
            email_subject_prefix: "[IbexaMailing]"
            email_from_address: "no-reply@code-rhapsodie.fr"
            email_from_name: "IbexaMailing"
            email_return_path: "return-path@code-rhapsodie.fr"
```

Example in dev mode

```yaml
framework:
    mailer:
        transports:
            main: '%env(MAILER_DSN)%'


ibexamailing:
    system:
        default:
            simple_mailer: "main"
            mailing_mailer: "main"
```

### Add the tables

```bash
bin/console ibexamailing:install
```

### Specify the Default Mailing List Id

To be able to implement the subscription form by doing subrequest to `ibexamailing_registration_default_create` route
the default Mailing List Id should be specified in the configuration file.
It should be added to the `default` section under the `ibexamailing` namespace.

If you need to the other mailing list id value for some particular site access it should be added to the corresponding section in the configuration file.

### Troubleshooting

If the bundle web assets (css, js etc.) are missing in the public directory it can be fixed by running the following commands:
```bash
bin/console assets:install --symlink --relative
bin/console assetic:dump
```
That will install bundles web assets under a public directory and dump them to the filesystem.
