exports.up = function (knex) {
    return knex.schema.createTable('consumerUsers', function (table) {
        table.increments('id');
        table
            .integer('consumerId')
            .unsigned()
            .notNullable()
            .references('id')
            .inTable('consumers')
            .onDelete('CASCADE');
        table
            .string('consumerUserId', 255)
            .nullable()
            .comment('User id given by the consumer.');
        table
            .string('userId', 255)
            .nullable()
            .comment(
                'The user id which is given through the jwt token. Refers to id in users table in auth API.'
            );
        table
            .uuid('deprecatedTenantId')
            .nullable()
            .unique()
            .comment(
                'This column should not be used in edlib2. used to migrate data from core'
            );
        table
            .timestamp('createdAt', { useTz: true })
            .notNullable()
            .defaultTo(knex.fn.now());
    });
};

exports.down = function (knex) {
    return knex.schema.dropTable('consumerUsers');
};
