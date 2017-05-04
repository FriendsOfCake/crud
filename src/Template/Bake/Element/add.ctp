<%
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
%>

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        <%
        $associatedModels = "";
        $associations = array_merge(
            $this->Bake->aliasExtractor($modelObj, 'BelongsTo'),
            $this->Bake->aliasExtractor($modelObj, 'BelongsToMany')
        );
        foreach ($associations as $assoc) {
            $association = $modelObj->association($assoc);
            $otherName = $association->target()->alias();
            $associatedModels .= "'" . $otherName . "', ";
        }
        $associatedModels = rtrim($associatedModels, ", ");
        if (!empty($associatedModels)) : 
        %>// Automatically executes find('list') on the 
        // associated tables that are either BelongsTo or BelongsToMany
        $associatedModels = [<%= $associatedModels %>];
        $this->Crud->listener('relatedModels')->relatedModels($associatedModels);

        // Use the relatedModel event to alter the query to fetch associated models
        // See http://crud.readthedocs.org/en/latest/listeners/related-models.html#events
        $this->Crud->on('relatedModel', function(Event $event) use ($associatedModels) {
            if (in_array($event->subject->association->name(), $associatedModels)) {
                $event->subject->query->limit(200);
            }
        });
        <% endif; %>

        return $this->Crud->execute();
    }
