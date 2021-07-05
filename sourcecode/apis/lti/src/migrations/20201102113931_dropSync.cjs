exports.up = function (knex) {
    return knex.schema.dropTable('syncs');
};

exports.down = function (knex) {};
