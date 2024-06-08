import {PrismaClient, wp_sp_cards} from './generated/client';
import {faker} from '@faker-js/faker';
// import {PrismaClient} from "@prisma/client";

const prisma = new PrismaClient()

async function main() {

    // Delete related.
    await prisma.wp_sp_deck_groups.deleteMany({
        where: {
            name: {
                contains: 'seed'
            }
        }
    })
    await prisma.wp_sp_decks.deleteMany({
        where: {
            name: {
                contains: 'seed'
            }
        }
    });
    await prisma.wp_sp_topics.deleteMany({
        where: {
            name: {
                contains: 'seed'
            }
        }
    });
    await prisma.wp_sp_card_groups.deleteMany({
        where: {
            name: {
                contains: 'seed'
            }
        }
    });
    await prisma.wp_sp_cards.deleteMany({
        where: {
            question: {
                contains: 'seed'
            }
        }
    })

    const user = await prisma.wp_users.upsert({
        where: {ID: 1, user_email: 'seed@prisma.io'},
        update: {},
        create: {
            user_email: 'seed@prisma.io',
            user_login: 'seed',
            user_pass: '$P$BDmjN1Hh/BoSjlwDGy8N2.Xy7xE8HU1',
            user_registered: faker.date.past()
        },
    })
    const userId = parseInt(user.ID.toString());

    // Create deck_group for user.
    const deck_group = await prisma.deck_groups.create({
        data: {
            name: 'seed Deck Group 1' + faker.lorem.words(3)
        }
    });

    const getDeckGroup = await prisma.deck_groups.findMany({
        where: {
            id: deck_group.id
        }
    })

    console.log({deck_group, getDeckGroup,deckId: deck_group.id})
    const deck = await prisma.wp_sp_decks.create({
        data: {
            name: 'seed Deck 1' + faker.lorem.words(2),
            deck_group_id: deck_group.id
        }
    });

    const topic = await prisma.wp_sp_topics.create({
        data: {
            name: "seed Topic 1",
            deck_id: deck.id
        }
    })

    const study = await prisma.wp_sp_study.create({
        data: {
            deck_id: deck.id,
            user_id: userId,
            all_tags: true,
            no_to_revise: 4,
            no_of_new: 3,
            no_on_hold: 5,
            active: true,
            revise_all: false,
            study_all_new: true,
            study_all_on_hold: false,
            topic_id: topic.id,
        }
    })

    const cardGroup = await prisma.wp_sp_card_groups.create({
        data: {
            name: "seed Card Group 1",
            topic_id: topic.id,
            deck_id: deck.id,
            card_type: "basic",
        }
    });

    Array(20).fill(null).forEach(async (c, index) => {
        const card = await prisma.wp_sp_cards.create({
            data: {
                card_group_id: cardGroup.id,
                c_number: index.toString(),
                question: `seed Question ${index}`,
                answer: ''
            }
        });

        Array(3).fill(null).forEach(async (a, aIndex) => {
            await prisma.wp_sp_answered.create({
                data: {
                    study_id: study.id,
                    card_id: card.id,
                    answer: `seed Answer c${card.id}`,
                    question: 'Question ',
                    grade: 'good',
                    ease_factor: 0.3,
                    created_at: faker.date.recent({
                        days: index + 1,
                    }),
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
            })
        })
    });


// console.log({henry})
}

main()
    .then(async () => {
        // await prisma.$disconnect()
    })
    .catch(async (e) => {
        console.error(e)
        // await prisma.$disconnect()
        // process.exit(1)
    })