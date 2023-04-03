---
slug: rewriting-the-future
title: Rewriting the future 
author: Tor-Martin Karlsen
author_url: https://github.com/tmkarlsen
author_image_url: https://github.com/tmkarlsen.png?size=460
tags: [edlib, refactor, cleanup, open-source, K8s, Meilisearch]
---

Since the last blog post, we've been continuously working on improving the Edlib "plant", but in reference to the previous blog post, we've concluded that we need to replant it.
This means we are now rewriting and consolidating most of the code to make further development more straightforward and secure. The need for a better overview and control has been growing simultaneously as the code base has grown. Part of the problem has been the use of many separate services, and now the decision has been made to move towards a monolithic Edlib. 
Besides being a big job, it will also provide us with an excellent opportunity to document and sort out flukes we've been unable to touch earlier. This enables us to reconsider the essence of Edlib - what to keep and what to throw away.

For instance, this means assessing and swapping out problematic or heavy services, such as Open Search, with the more lightweight [Meilisearch](https://docs.meilisearch.com/).
We'll also be looking into what systems for operations are sufficient and beneficial for Edlib. It is no secret that K8s is a resource-demanding system and is most likely overkill for the current needs. We are swapping K8s out with the serverless Lambda with Laravel Vapor on top of it.  
As the [roadmap](/docs/product/roadmap) indicates, this work will occur in the first half of 2023.

Sadly, Edstep - of the platforms using Edlib, had to be discontinued since the last blog post, but all the learning content used within Edstep lives on in Edlib and can be accessed through [Gamilab](https://gamilab.com). Perhaps Edstep will rise like a phoenix from the ashes one day. 

If you have any questions, feel free to [contact](/contact-us) us!

All the best from

The Edlib team



