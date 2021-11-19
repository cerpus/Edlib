exports.up = function (knex) {
    return knex.schema.createTable('ltiUsers', function (table) {
        table.uuid('id').notNullable().primary();
        table.string('clientId', 255).notNullable();
        table.string('deploymentId', 255).notNullable();
        table.string('externalId', 255).notNullable();
        table.string('email', 255);
        table.string('firstName', 255);
        table.string('lastName', 255);
        table
            .timestamp('lastSeen', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table
            .timestamp('updatedAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table.unique(['clientId', 'deploymentId', 'externalId']);
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('ltiUsers');
};
