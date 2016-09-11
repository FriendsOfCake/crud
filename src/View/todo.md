###Features:

- [X] automatically generate json api for entity collections using dynamic schema
- [X] automatically generate json api for single entities using dynamic schema
- [X] allow overrides by creating manual schema
- [X] add detector for `application/vnd.api+json` Accept request header 
- [X] set viewVars using Crud listener configuration options
- [X] respond with `application/vnd.api+json` Content-Type response header
- [X] suggest neomerx composer package instead of require
- [X] implement neomerx exception renderer for default errors
- [X] implement neomerx exception renderer for validation errors
- [X] add top-level `debug` and `query` nodes to exceptions
- [X] add top-level `query` node to 200 responses
- [ ] describe required custom route to support incoming requests to `self::SHOW_RELATED`
- [ ] decode incoming jsonapi data to cake compatible format
- [ ] why are NeoMarx options not respected (e.g. show-self-top-level)
- [ ] add support for `self::SHOW_SELF` and `self::SHOW_RELATED` inside `included` section
- [ ] fix missing SELF link in `included` member ?
- [ ] fix temporary hack in CrudComponent to prevent `undefined index` error on startup after enabling including the JsonApiListener
- [ ] document
- [ ] tests

###

- [ ] add jsonapi request type/check in both AppControllers (now disabled)



### self::SHOW_SELF

Generated link: http://api.app/v0/parties/1/relationships/country

### self::SHOW_RELATED

Generated link: http://api.app/v0/parties/1/country
