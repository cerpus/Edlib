# Edlib

Edlib is an [open-source](https://github.com/cerpus/Edlib/blob/master/LICENSE) application for creating, sharing, storing and using rich interactive learning resources in the cloud.

> The individual learner is at the center of everything we do and our vision is to provide first-class open learning tools and content to as many of those individuals as possible. &mdash; Tommy W. Nordeng (co-founder of Cerpus)

We strongly believe that the focus should be on the individual learner. Everything we do is based around this belief, all the way from technical solutions and content creation through to user interface design and the resulting learning experience. 

Edlib is being developed with :heart:&nbsp;&nbsp;by [Cerpus](https://cerpus.com/), a company based out of Northern Norway which is focused on developing innovative educational services and applications. Edlib was specificlly developed to allow for the creation and subsequent management of [H5P](https://h5p.org/)-based interactive learning resources for Cerpus's own applications &mdash;[Gamilab](https://gamilab.com/) and [Edstep](https://edstep.com/)&mdash; as well as being easily capable of integration into third-party learning applications. 

<img src="https://github.com/cerpus/Edlib/blob/master/sourcecode/docs/docs.edlib.com/static/img/edlib-content-explorer.png" alt="Edlib Content Explorer" width="800">

*Screenshot: Edlib Content Explorer*

## Why Edlib? 

Edlib allows you to create highly interactive H5P-based learning resources. The H5P platform provides a wealth of different interactive and rich content types for learning purposes. We believe that an increased level of meaningful **interactions** leads to a higher level of **engagement**. A higher level of engagement, in turn, leads to increased **motivation** ultimately resulting in a more positive **learning outcome** for the learner.

What's more, the internet is a huge collection of self-contained content repositories like Wikipedia and YouTube with many purpose-built tools to create rich interactive content. So, the question becomes: *How can we combine the vast trove of &mdash;existing, high-quality&mdash; content with the necessary creation tools to ensure a superior learning experience for the individual learner?* We think that, at least, a part of the answer to that question is to make the threshold for acquiring external content as low as possible. In Edlib, for example, a YouTube video can be converted into an Edlib resource by just providing its URL and from that point onwards it will be included in the Edlib content repository just like any other Edlib resource for the purpose of reuse.

<img src="https://github.com/cerpus/Edlib/blob/master/sourcecode/docs/docs.edlib.com/static/img/edlib-content-author.png" alt="Edlib Content Author" width="800">

*Screenshot: Edlib Content Author with interactive video content type editor*

## Feature Support

Edlib is continuously evolving with existing features being refined and new features being added. The following is a list of major features, several of which are in active development.

### Existing Features

* Support for all [H5P open-source content types](https://h5p.org/content-types-and-applications), some of which are highlighted below:
   * **Interactive video**: An interactive video content type allowing users to add multiple choice and fill in the blank questions, pop-up text and other types of interactions to their videos.
   * **Course presentation**: A presentation content type which allows users to add multiple choice questions, fill in the blanks, text, and other types of interactions to their presentations.
   * **Flash cards**: Interactive flashcards. Create a set of stylish and intuitive flashcards that have images paired with questions and answers. 
   * **Quiz**: A content type allowing creatives to create quizzes. Many question types are supported like multiple choice, fill-in-the-blanks, drag-the-words, mark-the-words and regular drag-and-drop.
* Content explorer to easily find and re-use existing Edlib content. Content can be filtered by H5P content type, tags, [Creative-Commons](https://creativecommons.org/) license and so forth.
* Create content-by-URL: the ability to point to an existing (publicly available) resource and turn it into an Edlib-based learning resource.
* Licensing module with an intelligent Creative-Commons license selector.
* Authoring workflows including the ability to maintain resources private or to make them publicly available.
* Collaboration functionality for content creators.
* Easy integration with third-party APIs including audio, video and image APIs.
* The ability to create quizzes and game-based learning activities quickly and easily from the integrated question bank.
* [Learning Tools Interoperability (LTI)](https://www.imsglobal.org/activity/learning-tools-interoperability) version 1.0/1.2 provider and consumer support.
* Resource versioning. 
* The ability to easily and quickly create language variants of the same underlying resource.

### Features in Development

* The &quot;Doku&quot; content type which allows for the bundling of multiple resources into a collection of resources and/or the further contextualisation of H5P-based resources with supplementary content &mdash;or instructions&mdash; effectively converting an interactive resource into a full-fledged **learning** resource. 
* The ability to include a recommendation engine to surface relevant content for course and game creators (currently in closed-beta).
* The Question Bank service to assist with the auto-generation of, for example, [H5P Question Sets](https://h5p.org/question-set) or educational games

## Installation

Documentation to help you install Edlib and start developing against it is currently being worked on. Check back for updates.

## Documentation

Visit [Edlib Docs](https://docs.edlib.com) to view the documentation. We are continuously working on updating and improving the documentation. If you see any errors, or didn't find what you were looking for, check in [issues](https://github.com/cerpus/Edlib/issues) and [discussions](https://github.com/cerpus/Edlib/discussions) here on Github before creating a new.

## How to Contribute

Cerpus welcomes contributions from anyone and everyone. Please see our [guidelines for contributing](https://github.com/Cerpus/Edlib/blob/master/CONTRIBUTING.md).

In general, we follow the "fork-and-pull" Git workflow:

 1. **Fork** the [repository](https://github.com/cerpus/Edlib) on GitHub
 2. **Clone** the project to your own machine
 3. **Commit** changes to your own branch
 4. **Push** your work back up to your fork
 5. Submit a **Pull request** so that we can review your changes. :) Make sure to add yourself to [AUTHORS](https://github.com/cerpus/Edlib/blob/master/AUTHORS.md).

### Translation

Edlib's user interface is multilingual. Support for new languages can be added without extensive technical knowledge. See the [Translating Edlib](https://docs.edlib.com/docs/developers/translation) section in the documentation for more info.

### Issues

If something isn't working the way you expected, please take a look at [previously logged issues](https://github.com/cerpus/Edlib/issues?q=is%3Aissue+is%3Aclosed) that resolve common problems first.  Have you found a new bug? Want to request a new feature? We'd love to hear from you.  Please let us know by submitting an [issue](https://github.com/cerpus/Edlib/issues).

## Translation status

| API Name             | Status                                         |
|----------------------|------------------------------------------------|
| API - Content author | [![Translation status](https://weblate.edlib.com/widgets/content-author/-/svg-badge.svg)](https://weblate.edlib.com/engage/content-author/)                 |
| NPM - Components     | [![Translation status](https://weblate.edlib.com/widgets/npm-components/-/svg-badge.svg)](https://weblate.edlib.com/engage/npm-components/) |

## Miscellaneous

### Metadata

Edlib includes the ability to record metadata on learning objects. The setting of metadata can be done both directly &mdash;on the object itself&mdash; or indirectly via transitive relations. Setting contextual metadata on learning objects is a means to an end, not an end in itself; that is, contextual metadata is about **findability** of content for both creators and learners. 

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
