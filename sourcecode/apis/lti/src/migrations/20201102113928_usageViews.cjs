exports.up = function (knex) {
    return knex.schema.createTable('usageViews', function (table) {
        table.uuid('id').notNullable().primary();
        table
            .uuid('usageId')
            .notNullable()
            .references('id')
            .inTable('usages')
            .onDelete('CASCADE');
        table
            .integer('consumerUserId')
            .unsigned()
            .nullable()
            .references('id')
            .inTable('consumerUsers')
            .onDelete('SET NULL');
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('usageViews');
};
