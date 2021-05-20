exports.up = function (knex) {
    return knex.schema.createTable('resourceGroups', function (table) {
        table.uuid('id').notNullable().primary();
        table
            .timestamp('updatedAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('resourceGroups');
};
