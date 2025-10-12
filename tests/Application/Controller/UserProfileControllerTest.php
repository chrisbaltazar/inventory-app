<?php

namespace App\Tests\Application\Controller;

use App\DataFixtures\Factory\UserFactory;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;
use App\Tests\Trait\WithUserSession;

class UserProfileControllerTest extends AbstractWebTestCase
{
    use WithUserSession;

    public function testOwnProfileUpdate(): void
    {
        $user = UserFactory::create();
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->asUser($this->client, $user)->request('GET', '/user/profile');

        $this->client->submitForm('Guardar', [
            'user_profile[name]' => 'Foo name',
            'user_profile[email][first]' => 'mail+99@test.com',
            'user_profile[email][second]' => 'mail+99@test.com',
            'user_profile[fullName]' => 'Foo Full Name',
            'user_profile[officialId]' => 'A1234',
            'user_profile[phone]' => '+34123456789',
            'user_profile[birthday]' => '1990-01-01',
        ]);

        $user = $this->client->getContainer()->get(UserRepository::class)->find($user->getId());

        self::assertSame('Foo name', $user->getName());
        self::assertSame('mail+99@test.com', $user->getEmail());
        self::assertSame('Foo Full Name', $user->getFullName());
        self::assertSame('A1234', $user->getOfficialId());
        self::assertSame('+34123456789', $user->getPhone());
        self::assertSame('1990-01-01', $user->getBirthday()->format('Y-m-d'));
        self::assertTrue($user->isProfileComplete());

        self::assertResponseRedirects('/');
    }
}
