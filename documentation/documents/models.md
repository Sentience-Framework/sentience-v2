# Models

Sentience offers Model classes that abstract database tables to php object.

## 1. Initializing a model

A Model class holds several required values:
```
protected string $table = '<table name>';

protected string $primaryKey = '<primary key property>';

protected bool $primaryKeyAutoIncrement = true;

protected array $columns = [
    'property' => 'column',
    'property' => 'column'
];
```

Each column is assigned to a property in the $columns variable. The following types are allowed:
- null (optional)
- int
- float
- string
- DateTime

Union types are not allowed, because most schema adhering databases don't support multiple types in one column.

## 2. Relations

### 2.1 Registering relations

Model relations need to be registered upon constructing the class. Override the constructor, and call the parent `__construct` first. Then, register all your relations by calling one of the following methods:
```
$this->hasOne();
$this->belongsTo();
$this->hasMany();
$this->manyToMany();
```

When registering the relation, it's always viewed from the perspective of the model.

### 2.2 Model to Relation joins

To make the relation easier to visualize, Sentience models use a string template to define the join. These are the following joins
```
has-one      : modelProperty->relationProperty
belongs-to   : modelProperty<-relationProperty
has-many     : modelProperty-<relationProperty
many-to-many : modelProperty-<jointable_model_property:jointable_relation_property>-relationProperty
```

With many to many relations, it purposefully doesn't use property names in the join table definition. Since Sentience doesn't support composite primary keys, this solution allows you to create, read, update, and delete join tables without having to turn them in to a model.

### 2.3 Hydrating relations

Make sure your model has a column defines, with either a ?Model or array type definition.

When retrieving your relations with the `selectRelation()` method, the property will be hydrated with the output from the select relation query.

## 3. CRUD methods

Models offer a basic create, read, update, and delete methods.
```
Model::select();
Model::selectRelation();
Model::insert();
Model::upsert();
Model::update();
Model::delete();
```

Each method allows you to modify the query being executed, by passing in a Closure (callable) that modifies the query.

## 4. Unique constraint

Models allow the automatic creation of unique constraints. Add the `protected array $unique` property to your model, and fill it with an array of property names.

If no unique properties are provided, the `upsert()` method uses the primary key as the list of columns.

## 5. CRUD hooks

Models allow the following overrides on CRUD hooks
```
Model::onSelect();
Model::onInsert();
Model::onUpdate();
Model::onDelete();
Model::onCreate();
Model::onDrop();
```

## 6. Constructing a model

A model takes two constructor args, a Database class and an optional stdClass (or any other object with public properties) that holds a database row with the default column names.

Here is how it looks like in action:
```
$results = $database->select()
    ->table(Migration:getTable())
    ->whereEquals('id', 1)
    ->execute();

if ($results->countRows() == 0) {
    return null;
}

return new Migration($database, $results->fetch());
```
