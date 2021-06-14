exports.up = function (knex) {
    return knex.schema.createTable('trackingResourceVersion', function (table) {
        table.increments('id');
        table
            .uuid('resourceVersionId')
            .notNullable()
            .references('id')
            .inTable('resourceVersions')
            .onDelete('CASCADE')
            .onUpdate('CASCADE');
        table
            .string('externalReference', 50)
            .nullable()
            .comment(
                'Currently only used to reference a ltiUsageView in the lti api. Can be used for any reference'
            );
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('trackingResourceVersion');
};
