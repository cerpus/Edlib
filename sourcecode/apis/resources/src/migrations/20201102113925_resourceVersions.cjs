exports.up = function (knex) {
    return knex.schema.createTable('resourceVersions', function (table) {
        table.uuid('id').notNullable().primary();
        table.uuid('resourceId').notNullable();
        table.string('externalSystemName', 50).notNullable();
        table.string('externalSystemId', 36).notNullable();
        table.text('title').notNullable();
        table.text('description').nullable();
        table.boolean('isPublished').notNullable().defaultsTo(false);
        table.boolean('isListed').notNullable().defaultsTo(false);
        table.string('license').nullable().defaultsTo(null);
        table.string('language').nullable().defaultsTo(null);
        table.string('contentType', 50).nullable().defaultsTo(null);
        table.string('ownerId', 100).notNullable();
        table
            .timestamp('updatedAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table.unique(['externalSystemName', 'externalSystemId']);
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('resourceVersions');
};
