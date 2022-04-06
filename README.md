Schema.org Blueprints
---------------------

Improve form validation

FAQPage
- https://developers.google.com/search/docs/advanced/structured-data/faqpage
- mainEntity: Question
- https://schema.org/FAQPage
- https://schema.org/Question
- https://schema.org/acceptedAnswer
- https://schema.org/Answer

HowTo
- https://developers.google.com/search/docs/advanced/structured-data/how-to

- Ongoing
- Determine the recommended types per entity type.
- Build out the default schema types properties.
- Review all description.
- Review patterns and tests.

Improve \Drupal\schemadotorg\Entity\SchemaDotOrgMapping::calculateDependencies
to support subtype.

# TBD

- How do we implement common content types and components?
  - teaser
  - slideshow
  - lists
  - forms
  - toc
  - faq
  - timeline

- Should you be able to map the same field to multiple properties?
  - body => description and disambiguatingDescription

- How do we handle sub-values (i.e. body.summary)?
  - Token field?

- How to handle translations for imported data?
  - Include descriptions added via the schemadotorg_descriptions.module

- How can we validate the generated JSON-LD?

- Should all the fields be prefixed with schema_* or field_*?
