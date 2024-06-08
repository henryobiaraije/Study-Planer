import {PrismaClient} from "./generated/client";

import {faker} from '@faker-js/faker';


const prisma = new PrismaClient();

async function main() {
    Array(20).fill(null).forEach(async (a, index) => {
        await prisma.wp_sp_answered.create({
            data: {
                study_id: 1,
                card_id: 1,
                answer: 'Answer ',
                question: 'Question ',
                grade: 'good',
                ease_factor: 0.3,
                next_due_at: faker.date.past(),
                next_due_answered: false,
                started_at: faker.date.past(),
                answered_as_new: true,
                answered_as_revised: '',
                next_interval: 3,
                rejected_at: faker.date.past(),
                card_last_updated_at: faker.date.past(),
                accept_changes_comment: '',
                answered_as_on_hold: false
            }
        });
    })
}

main()
    .then(async () => {
        await prisma.$disconnect();
    })
    .catch(async (e) => {
        console.error(e)
        await prisma.$disconnect();
    })