# Poliisiauto server

A server for an application where users can report bullying to a trusted adult.

See [this Google Drive document](https://docs.google.com/spreadsheets/d/1WYGZZfEpqy50AALHSY2IM9s3xUBot-i0YONzvU3Gz-4/edit#gid=1449701033) for initial server specification.

This server offers an API for various clients such as mobile applications (specifically for [PoliisiautoApp](https://github.com/Spacha/PoliisiautoApp)). The API has a public endpoint for authentication and a large set of protected endpoints for authenticated users.

See the complete **[API desctiption here](https://documenter.getpostman.com/view/3550280/2s8YzUwMLQ#auth-info-5fd01ded-b632-4259-b02d-26f74ddd579e)**.

## Authentication

The API requires authentication as the data is extremely sensitive. The API is mostly accessed per-user basis.
