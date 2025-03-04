---
slug: building-a-monolith
title: Building a monolith 
author: Tor-Martin Karlsen
author_url: https://github.com/tmkarlsen
author_image_url: https://github.com/tmkarlsen.png?size=460
tags: [edlib, refactor, coverage, open-source, getting started, documentation]
---

During the last quarter, we've been on a slow but sturdy path toward the monolith version of Edlib. As we consolidate and simplify the code, we've also been able to work on improving the test coverage in Content Author in particular. From the implementation of Codecov at the beginning of April, we've managed to improve from 54 % to 60 % coverage, which is quite a formidable change. Once we merge in the new monolith core of Edlib, we'll be able to present a more correct picture of the total coverage of the PHP code and the project in total.
We are also moving away from using Node in the core to more PHP. This does not limit the use of Node or any other language when creating content types, as the new architecture will be content type agnostic, meaning they will be more detached and bound to the core in a more generic way. This is done to make Edlib more attractive as a development platform and keep complexity down. 
 
A new design for the new Edlib Explorer is in the making, and we are doing the necessary research to ensure the UX and UI are done right and solve real problems for real users. We are also making sure Edlib works better on small devices through an improved responsive design.

We have also recently gone through the getting started instructions. By adding some missing documentation, fixing some failing processes, and, last but not least, making the instructions more precise, we aim to improve the process. 

For the next quarter, our main focus will be finishing up the work with the Edlib monolith and implementing the new design and making sure it's all compatible with Content Author. We'll also be doing a fair share of bugfixing and you can read the other planned activities in our updated [roadmap](/docs/product/roadmap). 

If you would like to know more about the project, or have any questions, feel free to [contact](/contact-us) us!

All the best

The Edlib team



