exports.up = function (knex) {
    return knex.schema.createTable('dokus', function (table) {
        table.uuid('id').primary();
        table.string('title', 255).notNullable();
        table.string('license', 50).notNullable();
        table.uuid('creatorId').notNullable();
        table.json('data').notNullable();
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
    return knex.schema.dropTable('dokus');
};
