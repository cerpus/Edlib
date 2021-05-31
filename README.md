# Edlib

EdLib is an [open-source](https://github.com/cerpus/Edlib/blob/master/LICENSE) application for creating, sharing, storing and using rich interactive learning resources in the cloud.

> The individual learner is at the center of everything we do. &mdash; Tommy Nordeng (Co-founder of Cerpus)

We strongly believe that the individual learner should be in focus. Everything we do is based around this belief, all the way from technical solutions and content creation through to user interface design and the learning experience. 

<img src="https://github.com/cerpus/Edlib/blob/master/sourcecode/docs/docs.edlib.com/static/img/edlib-content-explorer.png" alt="Edlib Content Explorer" width="800">

*Screenshot: Edlib Content Explorer*

## Why?

Edlib is being developed by [Cerpus](https://cerpus.com/), a company based out of Northern Norway which is focused on developing innovative educational services and applications. Edlib was specificlly developed to allow for the creation and subsequent management of [H5P](https://h5p.org/)-based interactive learning resources for Cerpus's own applications &mdash;[Gamilab](https://gamilab.com/) and [Edstep](https://edstep.com/)&mdash; as well as being easily capable of integration into third-party learning applications. 

So, Edlib... what's that all about? 

Well, the internet is a huge collection of self-contained content repositories like Wikipedia and YouTube with many purpose-built tools to create rich interactive content. So, the question becomes: *How can we combine the vast trove of &mdash;existing, high-quality&mdash; content with the necessary creation tools to ensure a superior learning experience for the individual learner?*

<img src="https://github.com/cerpus/Edlib/blob/master/sourcecode/docs/docs.edlib.com/static/img/edlib-content-author.png" alt="Edlib Content Author" width="800">

*Screenshot: Edlib Content Author with interactive video content type editor*

## Feature Support

### Existing Features

* Support for all [H5P open-source content types](https://h5p.org/content-types-and-applications), some of which are highlighted below:
   * **Interactive video**: An interactive video content type allowing users to add multiple choice and fill in the blank questions, pop-up text and other types of interactions to their videos.
   * **Course presentation**: A presentation content type which allows users to add multiple choice questions, fill in the blanks, text, and other types of interactions to their presentations.
   * **Flash cards**: Interactive flashcards. Create a set of stylish and intuitive flashcards that have images paired with questions and answers. 
   * **Quiz**: A content type allowing creatives to create quizzes. Many question types are supported like multiple choice, fill-in-the-blanks, drag-the-words, mark-the-words and regular drag-and-drop.
* Content browser to easily find and re-use existing Edlib content. Content can be filtered by H5P content type, tags, [Creative-Commons](https://creativecommons.org/) license and so forth.
* Create content-by-URL: the ability to point to an existing (publicly available) resource and turn it into an Edlib-based learning resource.
* Resource versioning.
* Language support.
* Licensing module with a Creative-Commons license selector.
* Authoring workflows including the ability to maintain resources private or to make them publicly available.
* Collaboration functionality.
* Easy integration with third-party APIs including audio, video and image APIs.
* The ability to create quizzes and game-based learning activities quickly and easily from the integrated question bank.

### Features in Development

* The *Doku* content type which allows for the bundling of multiple resources into a collection of resources and/or the further contextualisation of H5P-based resources with supplementary content &mdash;or instructions&mdash; effectively converting an interactive resource into a fully-fledged **learning** resource. 

## Installation

Documentation to help you install Edlib and start developing against it is currently being worked on. Check back for updates.

## Developer Documentation

We are currently working on developer-specific documentation and as soon as we have something meaningful we will make it available. Check back for updates.

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
