exports.up = function (knex) {
    return knex.schema.createTable('syncs', function (table) {
        table.increments('id');
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
    return knex.schema.dropTable('syncs');
};
