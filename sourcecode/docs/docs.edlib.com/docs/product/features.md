---
sidebar_position: 1
---

# Features

Edlib is continuously evolving with existing features being refined and new features being added. The following is a list of major features, several of which are in active development.

### Existing Features

* Support for all [H5P open-source content types](https://h5p.org/content-types-and-applications), some of which are highlighted below:
   * **Interactive video**: An interactive video content type allowing users to add multiple choice and fill in the blank questions, pop-up text and other types of interactions to their videos.
   * **Course presentation**: A presentation content type which allows users to add multiple choice questions, fill in the blanks, text, and other types of interactions to their presentations.
   * **Flash cards**: Interactive flashcards. Create a set of stylish and intuitive flashcards that have images paired with questions and answers. 
   * **Quiz**: A content type allowing creatives to create quizzes. Many question types are supported like multiple choice, fill-in-the-blanks, drag-the-words, mark-the-words and regular drag-and-drop.
* Content explorer to easily find and re-use existing Edlib content. Content can be filtered by H5P content type, tags, [Creative-Commons](https://creativecommons.org/) license and so forth.
* Create &lsquo;content-by-URL&rsquo;: the ability to reference an existing (publicly available) resource and turn it into an Edlib-based learning resource.
* Licensing module with an intelligent Creative-Commons license selector.
* Authoring workflows including the ability to maintain resources private or to make them publicly available.
* Collaboration functionality.
* Easy integration with third-party APIs including audio, video and image APIs.
* The ability to create quizzes and game-based learning activities quickly and easily from the integrated question bank.
* [Learning Tools Interoperability (LTI)](https://www.imsglobal.org/activity/learning-tools-interoperability) version 1.0/1.2 provider and consumer support.
* Integrate against the LMS of your choice. Through LTI you can track results directly in your learning management systems.
* More and better usage tracking with analysis will help you understand your students and create even better content.
* Resource versioning. 
* Language support.

#### H5P

H5P is a powerful content creation platform for learning resources. H5P makes it easy to create, share and reuse HTML5-based interactive content. H5P enables everyone to create rich, interactive learning experiences more efficiently.

H5P content is responsive and mobile-friendly allowing for the same rich, interactive content on desktop computers, smartphones and tablets alike.

#### Content Explorer

Edlib's Content Explorer allows you to easily find and re-use existing Edlib content. Content can be filtered by H5P content type, tags, [Creative-Commons](https://creativecommons.org/) license and so forth.

<div class="text--center">
    <img class="edlib-image" alt="Edlib Content Explorer" src="/img/edlib-content-explorer.png" />
    <br/>
    <em>Image: Edlib Context Explorer</em>
</div>

#### Content Author

Edlib's Content Author provides quick access to purpose-built content authoring environments for all [H5P open-source content types](https://h5p.org/content-types-and-applications). 

<div class="text--center">
    <img class="edlib-image" alt="Edlib Content Author: H5P interactive video editor" src="/img/edlib-content-author.png" />
    <br/>
    <em>Image: Edlib Content Author &mdash; <a href="https://h5p.org/interactive-video">H5P interactive video</a> editor</em>
</div>

#### Resource Versioning

Documentation in relation to the versioning of resources is currently being worked on. Check back for updates.

#### Language Support

Documentation in relation to language support is currently being worked on. Check back for updates.

### Features in Development

* The &quot;Doku&quot; content type which allows for the bundling of multiple resources into a collection of resources and/or the further contextualisation of H5P-based resources with supplementary content &mdash;or instructions&mdash; effectively converting an interactive resource into a full-fledged **learning** resource. 
* The ability to include a recommendation engine to surface relevant content for course and game creators (currently in closed-beta).
* The Question Bank service to assist with the auto-generation of, for example, [H5P Question Sets](https://h5p.org/question-set) or educational games

#### The &quot;Doku&quot; Content Type

The Doku content type is a block editor which allows the user to add block-based content (for example, text, H5Ps, videos and images) to a vertically-aligned collection of content. 

<div class="text--center">
    <img class="edlib-image" alt="Edlib Doku (block) editor" src="/img/edlib-doku.png" />
    <br/>
    <em>Image: Edlib Doku (block) editor</em>
    <br/>
    <br/>
</div>

From a combined pedagogical and technical point of view, the Doku content type provides the first level of formal context: it allows for the grouping of the individual resources themselves (and related information and instructions) into a larger context ultimately resulting in a full-fledged **learning** resource. The Edlib Content Explorer combined with the Edlib recommendation engine will provide the Doku (block) editor with access to the lowest *atomic* level of EdLib resources &mdash;H5Ps&mdash; from which the user can choose to build the contextualised learning resources. 

#### Recommendation Engine

The recommendation engine recommends and ranks relevant content (for course and game creators) based on three main information sources:

1. Content together with its metadata
2. Collections of content
3. Learning outcomes

<div class="text--center">
    <img class="edlib-image" alt="Recommendation Engine in the Edstep course builder" src="/img/edstep-recommendation-engine.png" />
    <br/>
    <em>Image: Recommendation Engine in the <a href="https://edstep.com/">Edstep</a> course builder (with suggestions from the Recommendation Engine on the right-hand side of the screen)</em>
    <br/>
    <br/>
</div>

What's more, the recommendation engine provides a set of APIs to work with content, collections and recommendations. Finally, the recommendation engine also provides an administrative interface to manually manage content, collections and to tweak ranking parameters.

#### Question Bank

The Edlib Question Bank is a separate Edlib service to manage question sets and related answers. Question sets can be tagged and categorized. What's more, individual answers have a &quot;degree of correctness&quot; property allowing for questions and the accompanying answers to go beyond the rigid *right vs. wrong* dichotomy.

<div class="text--center">
    <img class="edlib-image" alt="Edlib Question Bank high-level architecture" src="/img/edlib-question-bank-architecture.png" />
    <br/>
    <em>Image: Edlib Question Bank high-level architecture</em>
    <br/>
    <br/>
</div>

Currently, the Edlib Question Bank is used primarily to auto-generate [H5P Question Sets](https://h5p.org/question-set) or &quot;Who Wants to Be a Millionaire&quot;-like educational games. 