package controllers

import org.scalatestplus.play._
import org.scalatestplus.play.guice.GuiceOneAppPerTest
import play.api.test.Helpers._
import play.api.test._

class ApiDocsControllerSpec extends PlaySpec with GuiceOneAppPerTest {

  "Index" should {

    "redirect to Swagger UI" in {
      val request = FakeRequest(GET, "/")
      val posts = route(app, request).get

      status(posts) mustBe SEE_OTHER
      redirectLocation(posts) mustBe Some("/assets/lib/swagger-ui/index.html?url=http%3A%2F%2Flocalhost%3A9000%2Fswagger.json")
    }
  }
}