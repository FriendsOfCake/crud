# -*- coding: utf-8 -*-
import datetime
import sys
import os
import cakephp_theme
from sphinx.highlighting import lexers
from pygments.lexers.php import PhpLexer

sys.path.insert(0, os.path.abspath('.'))

# Pull in all the configuration options defined in the global config file..
from _config import *

########################
# Begin Customizations #
########################

maintainer = u'FriendsOfCake'
project = u'crud'
project_pretty_name = u'Crud'
copyright = u'%d, Friends of Cake' % datetime.datetime.now().year
version = '4.0'
release = '4.0'
html_title = 'Crud v4'
html_context = {
    'maintainer': maintainer,
    'project_pretty_name': project_pretty_name,
    'projects': {
        'Bootstrap UI': 'https://bootstrap-ui.readthedocs.io/',
        'CakePDF': 'https://cakepdf.readthedocs.io/',
        'Crud': 'https://crud.readthedocs.io/',
        'Crud Users': 'https://crud-users.readthedocs.io/',
        'Crud View': 'https://crud-view.readthedocs.io/',
        'CsvView': 'https://csvview.readthedocs.io/',
        'Search': 'https://friendsofcake-search.readthedocs.io/',
    }
}

htmlhelp_basename = 'crud'
latex_documents = [
    ('index', 'crud.tex', u'crud',
     u'Friends Of Cake', 'manual'),
]
man_pages = [
    ('index', 'crud', u'Crud Documentation',
     [u'Friends Of Cake'], 1)
]

texinfo_documents = [
    ('index', 'crud', u'Crud Documentation',
     u'Friends Of Cake', 'crud', 'CakePHP scaffolding on steroids!',
     'Miscellaneous'),
]

branch = 'master'

########################
#  End Customizations  #
########################

# -- General configuration ------------------------------------------------

extensions = [
    'sphinx.ext.todo',
    'sphinxcontrib.phpdomain',
    '_config.cakephpbranch',
]

templates_path = ['_templates']
source_suffix = '.rst'
master_doc = 'contents'
exclude_patterns = [
    '_build',
    '_themes',
    '_partials',
]

pygments_style = 'sphinx'
highlight_language = 'php'

# -- Options for HTML output ----------------------------------------------

html_theme = 'cakephp_theme'
html_theme_path = [cakephp_theme.get_html_theme_path()]
html_static_path = []
html_last_updated_fmt = '%b %d, %Y'
html_sidebars = {
    '**': ['globaltoc.html', 'localtoc.html']
}

# -- Options for LaTeX output ---------------------------------------------

latex_elements = {
}

lexers['php'] = PhpLexer(startinline=True)
lexers['phpinline'] = PhpLexer(startinline=True)
lexers['php-annotations'] = PhpLexer(startinline=True)
primary_domain = "php"
