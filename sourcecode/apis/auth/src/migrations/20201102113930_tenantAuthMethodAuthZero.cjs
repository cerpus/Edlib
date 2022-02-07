exports.up = function (knex) {
    return knex.schema.createTable(
        'tenantAuthMethodAuthZero',
        function (table) {
            table.uuid('tenantAuthMethodId').notNullable().primary().unique();
            table.string('domain', 255).notNullable();
            table.string('clientId', 255).notNullable();
            table.string('propertyPathId', 255).notNullable();
            table.string('propertyPathEmail', 255).notNullable();
            table.string('propertyPathName', 255).notNullable();
            table
                .timestamp('updatedAt', { useTz: true })
                .notNullable()
                .defaultTo(knex.fn.now());
            table
                .timestamp('createdAt', { useTz: true })
                .notNullable()
                .defaultTo(knex.fn.now());
        }
    );
};

exports.down = function (knex) {
    return knex.schema.dropTable('tenantAuthMethodAuthZero');
};
