Unit Testing
============

To ease with unit testing of Crud Listeners and Crud Actions, it's recommended
to use the proxy methods found in [CrudBaseObject]({{site.url}}/api/develop/class-CrudBaseObject.html).
<br />
<br />
These methods are much easier to mock than the full `CrudComponent` object.
<br />
<br />
They also allow you to just mock the methods you need for your specific test, rather than the big dependency nightmare the
CrudComponent can be in some cases.<br />

## Proxy methods

These methods are available in all `CrudAction` and `CrudListener` objects.

+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+
| Proxy method                                | Same as                                              | Description                                                    |
+=============================================+======================================================+================================================================+
| ``$this->_crud()``                          | ``$this->_container->crud``                          | Get the CrudComponent instance                                 |
+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+
| ``$this->_action($name)``                   | ``$this->_crud()->action($name)``                    | Get an CrudAction object by it's action name                   |
+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+
| ``$this->_trigger($eventName, $data = [])`` | ``$this->_crud()->trigger($eventName, $data = [])``  | Trigger a :doc:`Crud Event<events>`                            |
+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+
| ``$this->_listener($name)``                 | ``$this->_crud()->listener($name)``                  | Get a :doc:`Listener<listeners>` by its name                   |
+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+
| ``$this->_subject($additional = [])``       | ``$this->_crud()->getSubject($additional = [])``     | Get a :doc:`Listener<listeners>` by its name                   |
+---------------------------------------------+------------------------------------------------------+----------------------------------------------------------------+

  <tr>
    <td><code></code></td>
    <td><code></code></td>
    <td>Create a <a href="{{site.url}}/docs/events.html#global_accessible_subject_properties">Crud event subject</a> - used in <code>$this->_trigger</code></td>
  </tr>
    <tr>
    <td><code>$this->_session()</code></td>
    <td><code>$this->_crud()->Session</code></td>
    <td>Get the Session Component instance</td>
  </tr>
  <tr>
    <td><code>$this->_controller()</code></td>
    <td><code>$this->_container->controller</code></td>
    <td>Get the controller for the current request</td>
  </tr>
  <tr>
    <td><code>$this->_request()</code></td>
    <td><code>$this->_container->request</code></td>
    <td>Get the current CakeRequest for this request</td>
  </tr>
  <tr>
    <td><code>$this->_model()</code></td>
    <td><code>$this->_container->model</code></td>
    <td>Get the model instance that is created from <code>Controller::$modelClass</code></td>
  </tr>
</tbody>
</table>
