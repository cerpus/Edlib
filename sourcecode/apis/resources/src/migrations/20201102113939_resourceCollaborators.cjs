exports.up = function (knex) {
    return knex.schema.createTable('resourceCollaborators', function (table) {
        table.increments('id');
        table.uuid('applicationId').notNullable();
        table.string('context', 255).notNullable();
        table
            .uuid('resourceId')
            .notNullable()
            .references('id')
            .inTable('resources')
            .onDelete('RESTRICT')
            .onUpdate('CASCADE');
        table.string('tenantId', 36).nullable();
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('resourceCollaborators');
};
