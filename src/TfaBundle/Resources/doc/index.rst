TfaBundle
================

The application two-factor authentication bundle.

Usage
----------------

Inside `app/config/routing.yml` add
```
tfa:
    resource: "@TfaBundle/Controller/"
    type: annotation
```

Inside `app/config/config.yml` add
```
jms_translation:
    configs:
        TfaBundle:
            dirs:
                - "%kernel.root_dir%/../src/TfaBundle"
            output_dir: "%kernel.root_dir%/../src/TfaBundle/Resources/translations"
            default_output_format: xliff
```

Inside `src/AppBundle/Entity/User.php` add
`TfaBundle\Entity\Traits\User\TwoFactorAuthenticationTrait` as a trait
