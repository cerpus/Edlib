exports.up = function (knex) {
    return knex.schema.createTable('jobs', function (table) {
        table.increments('id');
        table.string('type', 255).notNullable();
        table
            .timestamp('startedAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
        table.integer('percentDone').notNullable().defaultsTo(0);
        table.string('message', 255).nullable();
        table.timestamp('doneAt', { useTz: true }).nullable();
        table.timestamp('failedAt', { useTz: true }).nullable();
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('jobs');
};
