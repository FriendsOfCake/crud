<%
use Cake\Utility\Inflector;

$defaultModel = $name;
%>
<?php
namespace <%= $namespace %>\Controller<%= $prefix %>;

use <%= $namespace %>\Controller\AppController;

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
echo $this->Bake->arrayProperty('components', $components, ['indent' => false]);
%>
}
