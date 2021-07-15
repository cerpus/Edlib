exports.up = function (knex) {
    return knex.schema.createTable('urls', function (table) {
        table.uuid('id').notNullable().primary();
        table.string('name', 255);
        table.text('url');
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
    return knex.schema.dropTable('urls');
};
