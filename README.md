# Edlib

EdLib is an application for creating, sharing, storing and using rich, interactive learning resources in the cloud.

## Why?

Edlib is being developed by [Cerpus](https://cerpus.com/), a company based out of Northern Norway which is focused on developing educational services and applications. Edlib was specificlly developed to allow for the creation and subsequent management of [H5P](https://h5p.org/)-based interactive learning resources for Cerpus's own applications &mdash;[Gamilab](https://gamilab.com/) and [Edstep](https://edstep.com/)&mdash; as well as being easily capable of integration into third-party learning applications. 

## Feature Support

### Existing Features

* Support for all of H5P's open-source [content types](https://h5p.org/content-types-and-applications)
* Content browser to easily find and re-use existing Edlib content
* Resource versioning
* Licensing module with a [Creative-Commons](https://creativecommons.org/) license selector
* Authoring workflows including the ability to maintain resources private or to publicly publish them
* Collaboration functionality 

### Features in Development

* &quot;Doku&quot; content type 

## Installation

Documentation to help you install Edlib and start developing against it is currently being worked on.

## Developer Documentation

We are currently working on developer-specific documentation and as soon as we have something meaningful we will make it available. 

## How to Contribute

1. Check for open issues or open a fresh issue to start a discussion around a feature idea or a bug.
2. Fork the [repository](https://github.com/cerpus/Edlib) on GitHub to start making your changes to the **master** branch (or branch off of it).
3. Write a test which shows that the bug was fixed or that the feature works as expected.
4. Send a pull request and bug the maintainer until it gets merged and published. :) Make sure to add yourself to [AUTHORS](https://github.com/cerpus/Edlib/blob/master/AUTHORS.md).

## Miscellaneous

### Environment variables

- EDLIBCOMMON_CONTENTAUTHOR_URL
- EDLIBCOMMON_CONTENTAUTHOR_CONSUMER_KEY
- EDLIBCOMMON_CONTENTAUTHOR_CONSUMER_SECRET
- EDLIBCOMMON_EXTERNALAUTH_JWKS_ENDPOINT
- EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_ID
- EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_EMAIL
- EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_FIRSTNAME
- EDLIBCOMMON_EXTERNALAUTH_PROPERTYPATH_LASTNAME
- EDLIBCOMMON_ELASTICSEARCH_URL
- EDLIBCOMMON_DB_HOST
- EDLIBCOMMON_DB_USER
- EDLIBCOMMON_DB_PASSWORD
- EDLIBCOMMON_DB_PORT
- EDLIBCOMMON_DB_PREFIX
- EDLIBCOMMON_CORE_INTERNAL_URL
