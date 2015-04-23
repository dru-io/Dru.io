Overview:

The configuration management module enables the ability to keep track of
specific configurations on a Drupal site, provides the ability to move these
configurations between different environments (local, dev, qa, prod), and also
move configurations between completely different sites (migrate configurations)
without the use of modules with all configuration being owned by the site.

For the most part this module provides the same functionality as a subset of the
features module. Features module is currently the go to tool for moving
configuration from environment to environment and site to site, and it does a
great job doing this, but "configuration management" was never really what
features was intended to do. The goal of features module was to group
configurations together to satisfy a certain use-case. Instead most people use
features to export configuration to code to ease with deployment between
environments. Many of us have experienced the shortcomings of using features
module for configuration management and found where it doesn't quite work to
easily manage configuration. A couple other modules have spawned off to help
with some of these shortcomings: features override, features plumber, Features
Tools.

This module takes some concepts from the Drupal 8 core configuration management
initiative, specifically the concept of the "activestore" and "datastore"
architecture. Read up on how Drupal 8 will manage configurations to get an idea
of how this module manages configuration between the activestore and datastore.

The module knows where configuration was changed and allows users to either
"activate" a configuration that was changed in the datastore, or "write" to
datastore if a configuration was changed in the activestore. If you enable the
diff module, you can see the what is in the activestore vs. datastore.

The granularity of managing configuration is down to each specific config,
rather than an entire group of configurations (feature module). This makes it
easier to activate or write to file specific configurations, without having
to "features-update" an entire group of configurations or "features-revert" an
entire group.

--------------------------------------------------------------------------------

Supported Configurations:

At this momment Configuration Management module have support for the following
components:

- Content Types
- Fields
- Vocabularies
- Text Formats
- Image Styles
- Variables
- Menu
- Menu Links
- Permissions

And for the following components if its contributed module is enabled.

- Wysiwyg (requires Wysiwyg 7.x-2.2 or greather)
- Views (requires views)
- Display Suite, Panels and other Ctools based modules (requries Ctools)
- Roles (requires role_export)

--------------------------------------------------------------------------------

Basic Concepts:

Active Store: Is where configurations lives. Content types, fields, image
styles are saved into the database. This is what is called Active Store.

Data Store: Is a set of files that represent the current being tracked
configurations. When a configuration is being tracked, all changes in the
active store can be reverted using the values of the Data Store and viceversa.

Track Configurations: Is the act to start detecting changes in configurations,
when a configuration is tracked, it is automatically exported to the data store.

Components: Represent group of configurations, content types, fields,
text formats, etc are diferent types of components.

Identifiers. Is a unique name that identify a configuration of a certain type of
component.




