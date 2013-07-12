---
title: View wish list
layout: default
---

# Crud View

Inspiration

- http://activeadmin.info/
- http://djangosuit.com/

## General

* Maybe as a secondary plugin called `crud-view`
* Dynamic, should not require files on disk for core features
** People can override functionality by providing their own widget / view paths
* Based on TwitterBootstrap (a theme that doesn't suck - with MIT license)
* Agnostic on authentication and authorization
* Responsive by default (Desktop & Mobile)

## Index

* Normal index lists
* "Scopes" (active, deleted etc.)
* "labels" (e.g. checkmark / minus for boolean) for fields in the list
* change order of fields
* configuration for each file (e.g. date format)
* Filters (Using CakeDC/search?)
** Date
** Search
* API links for current request
* Action items (Add new etc.)

## Form

* Basic `$this->Form->inputs()`
* Allow creation of tabbed forms / fieldsets
* Custom widgets for form fields (upload, date etc.)
* Nice selector of related records other than just selects (they suck for big lists)
