exports.up = function (knex) {
    return knex.schema.createTable(
        'resourceVersionCollaborators',
        function (table) {
            table.increments('id');
            table
                .uuid('resourceVersionId')
                .notNullable()
                .references('id')
                .inTable('resourceVersions')
                .onDelete('CASCADE')
                .onUpdate('CASCADE');
            table.string('email', 255).nullable();
            table.string('tenantId', 36).nullable();
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
    return knex.schema.dropTable('resourceVersionCollaborators');
};
