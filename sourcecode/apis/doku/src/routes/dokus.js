import express from 'express';
import dokuController from '../controllers/doku.js';
import { runAsync } from '@cerpus/edlib-node-utils';

const { Router } = express;

/**
 * @swagger
 *
 * definitions:
 *      Doku:
 *          type: object
 *          required:
 *              - data
 *          properties:
 *              data:
 *                  type: string
 *              title:
 *                  type: string
 * paths:
 *      /dokus:
 *          post:
 *              description: Create a new Doku
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - name: Doku
 *                    description: The data model for the new doku
 *                    in: body
 *                    required: true
 *                    schema:
 *                      $ref: "#/definitions/Doku"
 *              responses:
 *                  200:
 *                      description: Successfully created a new Doku
 *                  422:
 *                      description: Validation failed
 *          get:
 *              description: Get many dokus
 *              produces:
 *                  - application/json
 *              responses:
 *                  200:
 *                      description: Successfully returned dokus
 *      /dokus/{id}:
 *          post:
 *              description: Update a doku
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: id
 *                  - name: Doku
 *                    description: The data model for the new doku
 *                    in: body
 *                    required: true
 *                    schema:
 *                      $ref: "#/definitions/Doku"
 *              responses:
 *                  200:
 *                      description: Successfully created a new Doku
 *                  422:
 *                      description: Validation failed
 *          get:
 *              description: Get a doku
 *              produces:
 *                  - application/json
 *              parameters:
 *                  - in: path
 *                    name: id
 *              responses:
 *                  200:
 *                      description: Successfully found a Doku
 *                  404:
 *                      description: Didn't find the doku
 */
export default async () => {
    const router = Router();

    router.post('/users/:userId/dokus', runAsync(dokuController.create));
    router.patch(
        '/users/:userId/dokus/:dokuId',
        runAsync(dokuController.update)
    );
    router.post(
        '/users/:userId/dokus/:dokuId/publish',
        runAsync(dokuController.publish)
    );
    router.post(
        '/users/:userId/dokus/:dokuId/unpublish',
        runAsync(dokuController.unpublish)
    );
    router.get('/dokus/:dokuId', runAsync(dokuController.get));
    router.get(
        '/users/:userId/dokus/:dokuId',
        runAsync(dokuController.getForUser)
    );
    router.get('/dokus', runAsync(dokuController.getAll));

    return router;
};
