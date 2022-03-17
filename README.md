Schema.org Blueprints
---------------------
Slides: https://www.slidescarnival.com/valentine-free-presentation-template/234

Provides blueprints for leveraging Schema.org to build and manage an SEO and API-first content architecture in Drupal.

Demo

- Report
- Mappings
- Creation
  - Person
  - Thing > Event
  - Thing > Location
- Form and view display

# Common schemas

# Todo

The goal is to demo a content building framework which allows for progressive enhancements.

MUST deep dive the RDF module.
- Create RdfMapping that mirrors the SchemaDotOrgMapping.
- https://drupal.stackexchange.com/questions/241470/how-to-configure-rdf-on-fields

Review default properties that should always be created???
Thing, Person, Place, Event, Organization, CreativeWork

Review paragraphs support.

Remove adding only one Schema mapping per entity type.
Set entity reference handler default.

Reports
- Review field order.

Types of relations
- Datatype
- Name/Value
- Type

Enumerations
- http://localhost/so/admin/reports/schemadotorg/GenderType

Defined Terms
- http://localhost/so/admin/reports/schemadotorg/DefinedTerm
- Does this point to a taxonomy.

Relationships
- Can all relationships have text strings?

Review and document patterns provide by Google
https://developers.google.com/search/docs/advanced/structured-data/intro-structured-data

Enumeration
  schemedotorg_enumeration
  only for terms
  only for schemedotorg_enumeration vocabulary.

--------------------------------------------------------------------------------

Help text will better define the functionality.

Mapping
- property => field or field => property
- Sub properties???

- Add comment support.

- Reuse \Drupal\schemadotorg_ui\Form\SchemaDotOrgUiMappingForm::save

- Tree widget
  - https://github.com/vakata/jstree



- Subtyping.

Configure Schema.org and Schema.org UI
- General
  - Field prefix: 'schema_'
  - Excluded: Schema.org types
- Names:
  - Abbreviations
  - Suffixes
  - Prefixes

Schema.org Templates
  - Templates will preselect recommended fields.
  - Templates can be automatically updated

- Add help to types and properties reports.
  - Note Schema.org version.
  - Link to source CSV.

Define what is alpha beta and release goals.

--------------------------------------------------------------------------------

# Releases

Alpha
- Finalize drupal machine names.

# Tests

Entity

SchemaDotOrgMapping
- \Drupal\KernelTests\Core\Entity\EntityDisplayFormBaseTest
- \Drupal\KernelTests\Core\Entity\EntityDisplayRepositoryTest

Services

- SchemaDotOrgInstaller.php
- SchemaDotOrgBuilder.php
- SchemaDotOrgManager.php
- SchemaDotOrgNames.php

Report

- Filter form.
- Confirm types.
- Confirm properties.
- Confirm things.
- Confirm intangibles.
- Confirm enumerations.
- Confirm data types.
- Confirm names.
- Confirm warning.

UI
- FieldUIRouteTest.php

# TBD

- Should you be able to map the same field to multiple properties?
  - body => description and disambiguatingDescription

- Should Drupal names and ids be stored in the database or dynamically generated?

- Should the schema type be added to terms as field or property?

- Should we prefix all schema field with schema_*? YES

- Why are we seeing 1329 types? (/admin/reports/schemadotorg/docs/types)

- How to handle translations for imported data?

- How can we validate the generated JSON-LD?

# References

Schema.org

- [Understand how structured data works | Google Search Central](https://developers.google.com/search/docs/advanced/structured-data/intro-structured-data)
- [Schema.org - What, How, Why? | Video](https://www.youtube.com/watch?v=hcahQfN5u9Y)
- [RDF in Drupal: What is it and Why Should We Care? \ Drupal Easy](https://www.drupaleasy.com/blogs/ultimike/2009/06/rdf-drupal-what-it-and-why-should-we-care)

Drupal Entities & Field

- [Drupal content entity 8.0.pages](https://paperzz.com/doc/7052675/drupal-content-entity-8.0.pages)
- [Defining and using Content Entity Field definitions](https://www.drupal.org/docs/drupal-apis/entity-api/defining-and-using-content-entity-field-definitions)

# APIs

- **entity_type.manager:**
  Manages entity type plugin definitions.
  _Provides entity definition, handlers, storage, etc...
- **entity_type.repository:**
  Provides helper methods for loading entity types.
  _Gets entity types as options_
- **entity_type.bundle.info**
  Provides discovery and retrieval of entity type bundles.
  _Gets entity bundles_
- **entity.repository:**
  Provides several mechanisms for retrieving entities.
- **entity_display.repository:**
  Provides a repository for entity display objects (view modes and form modes).
- **entity_field.manager:**
  Manages the discovery of entity fields. This includes field definitions, base field definitions, and field storage definitions.


# Sub modules

- Report - Provides a report for browsing Schema.org types, properties, and naming conventions.
- Descriptions
- UI
- RDF - Generates Schema.org RDFa mapping via cores RDF module.
- Json-ld
- Json API
- Templates - Provides templates for creating and updating a Schema.org type in a click.
- Entity???

# Contrib modules

Required

- https://www.drupal.org/project/paragraphs
- https://www.drupal.org/project/key_value_field

Recommended
- https://www.drupal.org/project/entity_type_clone
- https://www.drupal.org/project/convert_bundles

Other
- https://www.drupal.org/project/flexfield
- https://www.drupal.org/project/computed_field

TBD
- https://www.drupal.org/project/field_token_value
- https://www.drupal.org/project/base_field_override_ui
- https://www.drupal.org/project/jsonapi_node_preview_tab
- https://www.drupal.org/project/field_ui_extras

# Schema.org Type => Drupal Entity

- Thing => Node
- Enumeration => Term
- Media Object => Media
- Structure values => Paragraph
- Component => Block content

# Common workflow

Create common intangible types via Paragraphs

PostalAddress
