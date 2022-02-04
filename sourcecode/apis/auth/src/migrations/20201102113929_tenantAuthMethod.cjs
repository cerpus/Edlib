exports.up = function (knex) {
    return knex.schema.createTable('tenantAuthMethods', function (table) {
        table.uuid('id').notNullable().primary();
        table.string('adapter', 255).notNullable();
        table.string('issuer', 255).notNullable().unique();
        table.string('jwksEndpoint', 255).notNullable();
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
    return knex.schema.dropTable('tenantAuthMethods');
};
