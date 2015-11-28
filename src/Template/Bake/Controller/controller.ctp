<%
use Cake\Utility\Inflector;

$defaultModel = $name;
%>
<?php
namespace <%= $namespace %>\Controller<%= $prefix %>;

use <%= $namespace %>\Controller\AppController;
<% if (!empty($actions)) : %>use Cake\Event\Event;<% endif; %>

/**
 * <%= $name %> Controller
 *
 * @property \<%= $namespace %>\Model\Table\<%= $defaultModel %>Table $<%= $defaultModel %>
<%
foreach ($components as $component):
    $classInfo = $this->Bake->classInfo($component, 'Controller/Component', 'Component');
%>
 * @property <%= $classInfo['fqn'] %> $<%= $classInfo['name'] %>
<% endforeach; %>
 */
class <%= $name %>Controller extends AppController
{
<%
echo $this->Bake->arrayProperty('helpers', $helpers, ['indent' => false]);
echo $this->Bake->arrayProperty('components', $components, ['indent' => false]);
%>
<% if (!empty($actions)) : %>
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);

        // you can remove all these mapActions if your AppController has mapped the actions via configuration
        // read http://crud.readthedocs.org/en/latest/configuration.html#actions for details
        // e.g. of a on-the-fly enable action
        // $this->Crud->mapAction('add', 'Crud.Add');
    }
<% endif; %>
<%
foreach($actions as $action) {
    echo $this->element('Controller/' . $action);
}
%>
}
