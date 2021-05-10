exports.up = function (knex) {
    return knex.schema.createTable('users', function (table) {
        table.string('id', 255).notNullable().unique();
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
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('users');
};
