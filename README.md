dmRelatedSelectsPlugin for Diem Extended
===============================

Author: [TheCelavi](http://www.runopencode.com/about/thecelavi)  
Version: 0.5.0  
Stability: Alpha (testing and improving)  
Date: December 5th, 2012  
Courtesy of [Run Open Code](http://www.runopencode.com)   
License: [Free for all](http://www.runopencode.com/terms-and-conditions/free-for-all)

Description
--------------

In the situation when you have one or several entities related as one(zero)-to-many, per 
example:

`Category` -> `Sub-category` -> `Product` 

and entity such as, per example, `PurchasedItem` where `PurchasedItem` is in `one-to-one` 
relation with `Product` the user input of the one `Product` can be quite hard to find the 
right one among several dozens, hundreds, etc. of it.

There for, these kind of use case scenarios are solved with filtering with related SELECTS.

Use case scenario can be explained as:

- User choose Category of the product from Category SELECT field
- The Sub-category SELECT field get filled with Sub-categories of selected Category
- User choose Sub-category of the product
- The SELECT field for Products get filled with products of selected Category and Sub-category

NOTE: this is just illustrative example what kind of problem this plugin solves.

How to use it?
----------------

The plugin contains the widget form class `sfWidgetFormDoctrineRelatedSelect` which extends
`sfWidgetFormDoctrineChoice`. It also has additional two javascript files:

- jquery.relatedselects.js
- launch.js

These two files are required, so have in mind that if you are using this form widget in your
front components to add a following code:

    public function executeExample(dmWebRequest $request)
    {
        $this->form = new MyFormWithRelatedSelects();
        // IMPORTANT: Adding JSs from FORM to response!
        foreach ($jscripts = $this->form->getJavaScripts() as $js) {
            $this->getResponse()->addJavaScript($js);
        }
		....
    }

Note as well that jquery.relatedselects.js is based on [jQuery related selects plugin](http://www.erichynds.com/jquery/jquery-related-dependent-selects-plugin/) but the
code is modified to suit this plugin.

When you have the plugin installed, in your forms it is required just to add instead of
plain selects the `sfWidgetFormDoctrineRelatedSelect`.

## Configuration

The widget extends the `sfWidgetFormDoctrineChoice` so it uses some of the options from
it:

- `model`: The model from witch SELECTS are populated
- `key_method`: The method from which OPTIONS get value attribute. Default is `getPrimaryKey`
- `method`: The method from which OPTIONS get labels. Default is `__toString`
- `table_method`: The SELECTS are populated based on the provided 'model' option. However, 
you can create specific table method in table class of the model and that method can be used.

`sfWidgetFormDoctrineRelatedSelect` uses options stated above and extends them with few more
options as well.

Required options are:

- `group`: Related selects are grouped. The name of the group is not as much important as the
fact that the group name is distinct. So for the related selects, the group name must be
the same and distinct from the other groups.
- `index`: Each field has its index. The top most has index with value 0. The next one has
index value 1 and so on. Note that index has to be zero based, increased by one (per example: 0, 1, 2...).

Optional options are:

- `parent`: If the related select has parent class (per example: Product is within
ProductCategory) the model has to be stated so the filtering can take place.
- `active_only`: When fetching objects, if `active_only` is `true` the generated query will have
a condition `WHERE is_active = true`

## i18N support

Yes! This plugin has i18n support.

## Example

Lets say we have a following entities:

	Category:
  	  columns:
		title: {type: string(255)}
		
	SubCategory:
	  columns:
	    title: {type: string(255)}
	    category_id: {type: integer, notnull: true}
	  relations:
	    Category:
	      class: Category
		  local: category_id
		  foreignAlias: SubCategories
	
	Product:
	  columns:
	    title: {type: string(255)}
	    sub_category_id: {type: integer, notnull: true}
	  relations:
	    SubCategory:
	      class: SubCategory
		  local: sub_category_id
		  foreignAlias: Products
	
	PurchasedItem:
	  columns:
	    title: {type: string(255)}
	    product_id: {type: integer, notnull: true}
	  relations:
	    Product:
	      class: Product
		  local: product_id
		  foreignAlias: PurchasedItems
		
So you want to create a form to create/edit a `PurchasedItem`? Purchased item would use
one SELECT control with the list of products. If you have a few hundreds of various products
the selection of right product is hard.

So, this can be solved by filtering products using related selects:


	class MyPurchasedItemForm extends dmForm
	{
	    public function configure()
	    {
	        $this->widgetSchema['category'] = new sfWidgetFormDoctrineRelatedSelect(array(
	            'add_empty' => 'Choose category',
	            'model' => 'Category',
	            'group' => 'my_group',
	            'index' => 0,
	            'active_only' => true
	        ));
	        $this->validatorSchema['category'] = new sfValidatorDoctrineChoice(array(
	            'required' => false,
	            'model' => 'Category'
	        ));
	
			$this->widgetSchema['sub_category'] = new sfWidgetFormDoctrineRelatedSelect(array(
	            'add_empty' => 'Choose sub category',
	            'model' => 'SubCategory',
	            'group' => 'my_group',
	            'index' => 1,
	            'active_only' => true,
				'parent' => 'Category'
	        ));
	        $this->validatorSchema['sub_category'] = new sfValidatorDoctrineChoice(array(
	            'required' => false,
	            'model' => 'SubCategory'
	        ));
        
	        $this->widgetSchema['product'] = new sfWidgetFormDoctrineRelatedSelect(array(
	            'add_empty' => 'Choose product',
	            'model' => 'Product',
	            'group' => 'my_group',
	            'index' => 2,
	            'active_only' => true,
	            'parent' => 'SubCategory'
	        ));
	        $this->validatorSchema['product'] = new sfValidatorDoctrineChoice(array(
	            'required' => false,
	            'model' => 'Product'
	        ));
			
			// ... Some more code here ...
        
	        parent::configure();               
	    }    
	}

So the example is self-explanatory. Please note `index` option and the values, as well as
who has `parent` option configured and who does not.
		