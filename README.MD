# phpBB server

Thinking different about phpBB. A home grown project.

I really want to play with React more and this is the way to share the outcome of my experiments with the world.

## Architecture

The biggest problem of old phpBB is a monolithic architecture that combines frontend and the backend.

In this implementation, architecture will be based on two main webservices: **Identity** and **phpBB** services tht will expose their functionality through a RESTful APIs.

Frontend of new solution will be fully separated from the backend (there will be no server side rendering in any form). Frontend code could be used by multiple installations or custom implementation can be used.

Frontend will be using a React based single page application to render the content.

There will be no admin application.

Building extensions will not require any server changes, as universal usage metadata fields will be available for core entities.

The end goal is to build a compact, easy to maintain solution that will be able to replace obsolete phpBB installations.

## Identity service

Identity service will be responsible for authentication, organisations (groups, roles, etc.), permissions and users management.

Authentication will allow to build custom methods, next to a default login and password authentication, such as Facebook, Google, UUM, OAuth2 or U2F methods.

Authorization within Identity and phpBB service will be based on JWT tokens. Both services will require to call a /verify endpoint in Identity service to retrieve permissions for given bearer token.

### Setup

There's no setup script at this stage. To run Identity service, just run `docker compose up -d` command. 

On MongoDB server, add a `phpbb-auth` database.

In new database add `authentication`, `members`, `organisations`, `policies` and `users` collections.

## phpBB service

Main difference is resignation from using category, forum, topic and posts topology. Instead, there will be only categories that could be used to recreate a hierarchy. Topics and posts will be replaced by unified thread object that based on references and the metadata could be used to render content in expected format, eg:

 * Topic view (like in classic phpBB)
 * Post view (like on X)
 * Gallery view (like on Instagram)
 * Articles view (like in Wordpress)

and many more.

Extending functionality will be only a frontend change. Extensions like avatars, birthdays, pages, notifcation etc, will utilize entities metadata fields.

Having identity and phpbb services separated, will allow to distribute the data and provide a multi channel communication. 

In future, possible chunk encryption can be implement to protect the contents from unauthorized access.

### Setup

Not available

## Future work

* Swagger generator
* Add MySQL support
* Build a phpBB service
* Build a React single page application

## Collaboration

If you want to join the effort, fork the repo and create merge requests from your fork.

Before creating Merge Request, run Postman collection from `tests/` directory, to make sure they are not broken.

## Timeframe

Over last many years, I wanted to modernise **phpBB** and **phpBB by Przemo**, unfortunately the code is not very friendly. It still remembers php4 and any (failed) attempts to refactor the code took long time. Instead, is easier to build everything from scratch (yeah, I know how it sounds).

* Completing the Identity service - *end of March 2024*
* Completing the phpBB service - *end of April 2024*
* Building first version of the frontend - *end of August 2024*

More to be defined later.

## Mocks

TBC

## Why am I not using any framework?

Because I don't want to. I would like to keep mistakes / bugs / security issues on me only. Frameworks gets old very fast. They loose support after few years and usually require significant refactoring.

The core functionality of this script is already built and is small. Looking on the attempts to adopt Symfony framework by original phpBB group, discourages me from following their mistake.

If you don't like it, I'm sorry.

## Authors

**Irek Kubicki** ✉️ [github@ixdude.com](mailto:github@ixdude.com?subject=phpBB%20question) 🌍 [www.codebuilders.pl](https://www.codebuilders.pl)

