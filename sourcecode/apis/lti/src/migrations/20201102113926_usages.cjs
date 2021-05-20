exports.up = function (knex) {
    return knex.schema.createTable('usages', function (table) {
        table.uuid('id').notNullable().primary();
        table
            .integer('consumerId')
            .unsigned()
            .nullable()
            .references('id')
            .inTable('consumers')
            .onDelete('SET NULL')
            .onUpdate('CASCADE');
        table.uuid('resourceId').notNullable();
        table.uuid('resourceVersionId').nullable();
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('usages');
};
