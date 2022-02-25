exports.up = function (knex) {
    return knex.schema.createTable('resourceSearchFilters', function (table) {
        table.increments('id');
        table.integer('resourceSearchId').notNullable();
        table.string('groupName', 255).notNullable();
        table.string('value', 255).notNullable();
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('resourceSearchFilters');
};
