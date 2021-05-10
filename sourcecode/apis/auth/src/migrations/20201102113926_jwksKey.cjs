exports.up = function (knex) {
    return knex.schema.createTable('jwksKeys', function (table) {
        table.increments('id');
        table.text('key').notNullable();
        table.timestamp('expiresAt', { useTz: true }).nullable();
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('jwksKeys');
};
