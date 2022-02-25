exports.up = function (knex) {
    return knex.schema.createTable('resourceSearches', function (table) {
        table.increments('id');
        table.string('userId').nullable();
        table.string('contentFilter', 255).notNullable();
        table.string('searchString').nullable();
        table.string('orderBy').notNullable();
        table.integer('offset').notNullable();
        table.integer('limit').notNullable();
        table
            .timestamp('searchedAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('resourceSearches');
};
