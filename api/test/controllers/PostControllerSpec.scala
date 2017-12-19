package controllers

import models.{Post, PostRepository}
import org.scalamock.scalatest.MockFactory
import org.scalatestplus.play._
import org.scalatestplus.play.guice._
import play.api.inject.bind
import play.api.inject.guice.GuiceApplicationBuilder
import play.api.test.Helpers._
import play.api.test._

import scala.concurrent.ExecutionContext.Implicits.global
import scala.concurrent.Future

class PostControllerSpec extends PlaySpec
  with GuiceOneAppPerTest
  with Injecting
  with MockFactory {

  val mockRepository: PostRepository = mock[PostRepository]

  override def fakeApplication() = new GuiceApplicationBuilder()
    .overrides(bind[PostRepository].toInstance(mockRepository))
    .build()

  "/posts" should {

    "return an empty list of posts" in {
      mockRepository.getAll _ expects() returning Future(Seq())

      val request = FakeRequest(GET, "/posts")
      val posts = route(app, request).get

      status(posts) mustBe OK
      contentType(posts) mustBe Some(JSON)
      //TODO: Json matcher like Specs2 would be nice
      contentAsString(posts) mustBe "{\"posts\":[],\"count\":0}"
    }

    "return a list of posts" in {
      mockRepository.getAll _ expects() returning Future(Seq(
        Post(None, Some("VMD"), Some("Someone"), Some("2017")))
      )

      val request = FakeRequest(GET, "/posts")
      val posts = route(app, request).get

      status(posts) mustBe OK
      contentType(posts) mustBe Some(JSON)
      contentAsString(posts) mustBe "{\"posts\":[{\"content\":\"VMD\",\"author\":\"Someone\",\"date\":\"2017\"}],\"count\":1}"
    }
  }

  "/posts/:id" should {
    "return not found status for missing post" in {
      mockRepository.getOne _ expects * returning Future(None)

      val request = FakeRequest(GET, "/posts/5a3841ab1500001500457e55")
      val posts = route(app, request).get

      status(posts) mustBe NOT_FOUND
    }

    "return a single post" in {
      mockRepository.getOne _ expects * returning Future(Some(Post(None, Some("Test"), None, None)))

      val request = FakeRequest(GET, "/posts/5a3841ab1500001500457e55")
      val posts = route(app, request).get

      status(posts) mustBe OK
      contentType(posts) mustBe Some(JSON)
      contentAsString(posts) mustBe "{\"post\":{\"content\":\"Test\"}}"
    }
  }
}
