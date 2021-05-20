exports.up = function (knex) {
    return knex.schema.createTable('resources', function (table) {
        table.uuid('id').notNullable().primary();
        table
            .uuid('resourceGroupId')
            .notNullable()
            .references('id')
            .inTable('resourceGroups')
            .onDelete('RESTRICT')
            .onUpdate('CASCADE');
        table.text('deletedReason').nullable();
        table
            .timestamp('deletedAt', { useTz: true })
            .nullable()
            .defaultTo(null);
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
    return knex.schema.dropTable('resources');
};
