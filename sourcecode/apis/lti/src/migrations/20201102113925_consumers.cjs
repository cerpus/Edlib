exports.up = function (knex) {
    return knex.schema.createTable('consumers', function (table) {
        table.increments('id');
        table.string('key', 100).notNullable().unique();
        table.string('secret', 100).notNullable();
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('consumers');
};
