exports.up = function (knex) {
    return knex.schema.createTable('usageViews', function (table) {
        table.uuid('id').notNullable().primary();
        table.uuid('usageId').notNullable();
        table.string('tenantId', 100).nullable();
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('usageViews');
};
